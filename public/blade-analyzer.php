<?php
// Este script analizará un archivo Blade y encontrará directivas que no coincidan

// Función para cargar un archivo y devolver su contenido
function cargarArchivo($ruta) {
    if (!file_exists($ruta)) {
        return "El archivo no existe: " . $ruta;
    }
    return file_get_contents($ruta);
}

// Función para analizar las directivas de Blade
function analizarDirectivas($contenido) {
    // Directivas de apertura y cierre que debemos rastrear
    $directivas = [
        'if' => 'endif',
        'foreach' => 'endforeach',
        'for' => 'endfor',
        'while' => 'endwhile',
        'section' => 'endsection',
        'push' => 'endpush',
        'php' => 'endphp',
    ];
    
    // Almacenar directivas abiertas
    $pila = [];
    
    // Resultados
    $resultados = [
        'coincidencias' => [],
        'no_coincidentes' => [],
        'lineas' => []
    ];
    
    // Dividir el contenido por líneas
    $lineas = explode("\n", $contenido);
    
    // Iterar por cada línea
    foreach ($lineas as $numeroLinea => $linea) {
        $numeroLinea++; // Ajustar para que empiece desde 1
        
        // Buscar directivas de apertura
        foreach ($directivas as $apertura => $cierre) {
            $patron = "/@{$apertura}(?:\s*\([^)]*\)|[^\w])/i";
            if (preg_match_all($patron, $linea, $coincidencias)) {
                foreach ($coincidencias[0] as $coincidencia) {
                    $pila[] = [
                        'tipo' => $apertura,
                        'linea' => $numeroLinea,
                        'contenido' => trim($linea)
                    ];
                    $resultados['lineas'][$numeroLinea] = [
                        'tipo' => 'apertura',
                        'directiva' => $apertura,
                        'contenido' => trim($linea)
                    ];
                }
            }
        }
        
        // Buscar directivas de cierre
        foreach ($directivas as $apertura => $cierre) {
            if (strpos($linea, "@{$cierre}") !== false) {
                $encontrado = false;
                
                // Recorrer la pila en orden inverso
                for ($i = count($pila) - 1; $i >= 0; $i--) {
                    if ($pila[$i]['tipo'] === $apertura) {
                        // Crear un registro de coincidencia
                        $resultados['coincidencias'][] = [
                            'apertura' => $pila[$i],
                            'cierre' => [
                                'tipo' => $cierre,
                                'linea' => $numeroLinea,
                                'contenido' => trim($linea)
                            ]
                        ];
                        
                        $resultados['lineas'][$numeroLinea] = [
                            'tipo' => 'cierre',
                            'directiva' => $cierre,
                            'contenido' => trim($linea),
                            'coincide_con' => $pila[$i]['linea']
                        ];
                        
                        // Eliminar de la pila
                        array_splice($pila, $i, 1);
                        $encontrado = true;
                        break;
                    }
                }
                
                if (!$encontrado) {
                    $resultados['no_coincidentes'][] = [
                        'tipo' => 'cierre_sin_apertura',
                        'directiva' => $cierre,
                        'linea' => $numeroLinea,
                        'contenido' => trim($linea)
                    ];
                }
            }
        }
    }
    
    // Las directivas que quedaron en la pila no tienen cierre
    foreach ($pila as $directiva) {
        $resultados['no_coincidentes'][] = [
            'tipo' => 'apertura_sin_cierre',
            'directiva' => $directiva['tipo'],
            'linea' => $directiva['linea'],
            'contenido' => $directiva['contenido']
        ];
    }
    
    return $resultados;
}

// Función para mostrar resultados
function mostrarResultados($resultados) {
    echo "<h2>Análisis de Directivas Blade</h2>";
    
    if (empty($resultados['no_coincidentes'])) {
        echo "<p style='color: green; font-weight: bold;'>✅ Todas las directivas coinciden correctamente.</p>";
    } else {
        echo "<p style='color: red; font-weight: bold;'>❌ Se encontraron directivas sin coincidencia:</p>";
        echo "<ul>";
        foreach ($resultados['no_coincidentes'] as $noCoincidente) {
            if ($noCoincidente['tipo'] === 'apertura_sin_cierre') {
                echo "<li>Línea {$noCoincidente['linea']}: Directiva de apertura <code>@{$noCoincidente['directiva']}</code> sin su correspondiente cierre <code>@end{$noCoincidente['directiva']}</code>.<br>Contenido: <code>" . htmlspecialchars($noCoincidente['contenido']) . "</code></li>";
            } else {
                echo "<li>Línea {$noCoincidente['linea']}: Directiva de cierre <code>@{$noCoincidente['directiva']}</code> sin su correspondiente apertura.<br>Contenido: <code>" . htmlspecialchars($noCoincidente['contenido']) . "</code></li>";
            }
        }
        echo "</ul>";
    }
    
    echo "<h3>Listado de directivas por línea:</h3>";
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
    echo "<tr><th>Línea</th><th>Tipo</th><th>Directiva</th><th>Contenido</th><th>Coincide con línea</th></tr>";
    
    ksort($resultados['lineas']);
    
    foreach ($resultados['lineas'] as $numeroLinea => $info) {
        $estilo = $info['tipo'] === 'apertura' ? 'background-color: #e0f7fa;' : 'background-color: #fff9c4;';
        echo "<tr style='{$estilo}'>";
        echo "<td>{$numeroLinea}</td>";
        echo "<td>{$info['tipo']}</td>";
        echo "<td>@{$info['directiva']}</td>";
        echo "<td>" . htmlspecialchars($info['contenido']) . "</td>";
        echo "<td>" . (isset($info['coincide_con']) ? $info['coincide_con'] : '-') . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
}

// Ruta del archivo a analizar
$rutaArchivo = __DIR__ . '/../resources/views/admin/usuarios.blade.php';

// Cargar y analizar el archivo
$contenido = cargarArchivo($rutaArchivo);
$resultados = analizarDirectivas($contenido);

// Mostrar cabecera HTML
echo "<!DOCTYPE html><html><head><title>Analizador de Blade</title><style>body{font-family:Arial,sans-serif;margin:20px;}</style></head><body>";

// Mostrar los resultados
mostrarResultados($resultados);

// Mostrar el contenido del archivo con números de línea
echo "<h3>Contenido del archivo con números de línea:</h3>";
echo "<pre style='background-color: #f5f5f5; padding: 10px; overflow: auto; max-height: 500px;'>";
$lineas = explode("\n", $contenido);
foreach ($lineas as $numero => $linea) {
    $numero++; // Ajustar para que empiece desde 1
    $numFormateado = str_pad($numero, 4, '0', STR_PAD_LEFT);
    echo "{$numFormateado}: " . htmlspecialchars($linea) . "\n";
}
echo "</pre>";

echo "</body></html>";
