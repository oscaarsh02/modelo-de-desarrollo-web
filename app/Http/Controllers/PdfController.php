<?php

namespace App\Http\Controllers;

use App\Models\Materia;
use App\Models\Grupo;
use App\Models\Salon;
use App\Models\Horario;

use Illuminate\Http\Request;
use Smalot\PdfParser\Parser;


class PdfController extends Controller
{
    public function form()
    {
        return view('subir_pdf');
    }

    public function procesar(Request $request)
    {
        $archivo = $request->file('archivo');

        $parser = new Parser();
        $pdf = $parser->parseFile($archivo->getPathname());

        $texto = $pdf->getText();

        // convertir todo el PDF en líneas
        $lineas = explode("\n", $texto);

        echo "<pre>";



        foreach ($lineas as $linea) {

    if (preg_match('/^[0-9]{5}/', trim($linea))) {

        $datos = preg_split('/\s+/', trim($linea));

        $nrc = $datos[0];
        $clave = $datos[1].$datos[2];

        $hora = '';
        $dia = '';
        $grupo = '';
        $salon = '';

        foreach ($datos as $dato) {

            // detectar día
            if(in_array($dato, ['L','M','A','J','V','S'])){
                $dia = $dato;
            }

            // detectar hora
            if(preg_match('/^[0-9]{4}-[0-9]{4}$/',$dato)){
                $hora = $dato;
            }

            // detectar grupo (OO1, OO2, etc)
            if(preg_match('/^OO[0-9]/',$dato)){
                $grupo = $dato;
            }

            // detectar salón
            if(str_contains($dato,'/')){
                $salon = $dato;
            }

        }


// guardar materia
$materia = Materia::firstOrCreate([
    'clave' => $clave
]);

// guardar grupo
$grupoDB = Grupo::firstOrCreate([
    'nombre' => $grupo
]);

// guardar salón
$salonDB = Salon::firstOrCreate([
    'nombre' => $salon
]);

// guardar horario
Horario::create([
    'nrc' => $nrc,
    'materia_id' => $materia->id,
    'grupo_id' => $grupoDB->id,
    'salon_id' => $salonDB->id,
    'dia' => $dia,
    'hora' => $hora
]);

        echo "NRC: ".$nrc." | ";
        echo "CLAVE: ".$clave." | ";
        echo "GRUPO: ".$grupo." | ";
        echo "DIA: ".$dia." | ";
        echo "HORA: ".$hora." | ";
        echo "SALON: ".$salon."<br><br>";

    }
}

    } 
}