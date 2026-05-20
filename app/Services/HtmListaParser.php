<?php

namespace App\Services;

class HtmListaParser
{
    /**
     * Parsea el archivo HTM del SAES BUAP y retorna la información del curso y la lista de alumnos.
     *
     * @param  string  $html  Contenido HTML del archivo
     * @return array{curso: array, alumnos: array}
     */
    public function parse(string $html): array
    {
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($html);
        libxml_clear_errors();

        $xpath = new \DOMXPath($dom);

        $curso = $this->parseCurso($xpath);
        $alumnos = $this->parseAlumnos($xpath);

        return compact('curso', 'alumnos');
    }

    private function parseCurso(\DOMXPath $xpath): array
    {
        $curso = [
            'nombre' => '',
            'clave'  => '',
            'nrc'    => '',
            'grupo'  => '',
        ];

        // Encabezado de curso: "Visión y Animación por Computadora - ICCS 616 001"
        $captionNodes = $xpath->query('//caption[contains(@class,"captiontext")]');
        foreach ($captionNodes as $caption) {
            $text = trim($caption->textContent);
            if (str_contains($text, '-')) {
                $parts = explode('-', $text, 2);
                $curso['nombre'] = trim($parts[0]);
                // "ICCS 616 001" → clave = "ICCS 616", grupo = "001"
                $resto = trim($parts[1] ?? '');
                if (preg_match('/^(\S+\s+\S+)\s+(\S+)$/', $resto, $m)) {
                    $curso['clave'] = trim($m[1]);
                    $curso['grupo'] = trim($m[2]);
                } elseif (preg_match('/^(.+)\s+(\S+)$/', $resto, $m)) {
                    $curso['clave'] = trim($m[1]);
                    $curso['grupo'] = trim($m[2]);
                } else {
                    $curso['clave'] = $resto;
                }
            }
        }

        // NRC
        $nrcNodes = $xpath->query('//th[contains(@class,"ddlabel") and contains(text(),"NRC")]/following-sibling::td[1]');
        foreach ($nrcNodes as $node) {
            $curso['nrc'] = trim($node->textContent);
            break;
        }

        return $curso;
    }

    private function parseAlumnos(\DOMXPath $xpath): array
    {
        // Extraer correos del listado de mailto links individuales (no el BCC masivo)
        $correosPorPosicion = [];
        $mailLinks = $xpath->query('//td[contains(@class,"dddefault")]//a[starts-with(@href,"mailto:") and not(contains(@href,"?Bcc"))]');
        foreach ($mailLinks as $link) {
            $href = $link->getAttribute('href');
            $email = str_replace('mailto:', '', $href);
            if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $correosPorPosicion[] = strtolower(trim($email));
            }
        }

        // Tabla principal de alumnos: rows con número, nombre, ID, status, nivel, créditos
        $alumnos = [];
        $emailIndex = 0;

        // Buscar la tabla que contiene "Resumen de Lista de Clase"
        $tablaNodes = $xpath->query('//table[contains(@summary,"lista de alumnos") or contains(@summary,"Lista de Clase") or contains(@summary,"alumnos inscritos")]');

        foreach ($tablaNodes as $tabla) {
            $filas = $xpath->query('.//tr', $tabla);
            foreach ($filas as $fila) {
                $celdas = $xpath->query('.//td[contains(@class,"dddefault")]', $fila);
                if ($celdas->length < 3) {
                    continue;
                }

                $num      = trim($celdas->item(0)->textContent);
                $nombre   = trim($celdas->item(1)->textContent);
                $matricula = trim($celdas->item(2)->textContent);

                // Validar que sea una fila de alumno (número entero, matrícula numérica)
                if (! is_numeric($num) || ! preg_match('/^\d{6,12}$/', $matricula)) {
                    continue;
                }

                // Asignar correo por posición (los emails están ordenados igual que los alumnos)
                $correo = $correosPorPosicion[$emailIndex] ?? null;
                $emailIndex++;

                $alumnos[] = [
                    'nombre'    => $this->normalizarNombre($nombre),
                    'matricula' => $matricula,
                    'correo'    => $correo,
                ];
            }
        }

        return $alumnos;
    }

    /**
     * Convierte "APELLIDO APELLIDO, NOMBRE NOMBRE" → "NOMBRE NOMBRE APELLIDO APELLIDO"
     */
    private function normalizarNombre(string $nombre): string
    {
        $nombre = trim($nombre);

        if (str_contains($nombre, ',')) {
            [$apellidos, $nombres] = explode(',', $nombre, 2);
            return trim($nombres) . ' ' . trim($apellidos);
        }

        return $nombre;
    }
}
