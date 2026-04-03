<?php

namespace Database\Seeders;

use App\Models\Classroom;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // --- Salones ---
        $salonA = Classroom::create(['name' => 'Salon A', 'capacity' => 3, 'location' => 'Planta Baja']);
        $salonB = Classroom::create(['name' => 'Salon B', 'capacity' => 4, 'location' => 'Primer Piso']);

        // Cursos y temarios
        $coursesData = [
            ['name' => 'DJ Basico',            'description' => 'Introduccion al mundo del DJ.',     'lessons' => ['Historia del DJ y la musica electronica','Conociendo el equipo: platos y mezcladora','Beatmatching: empalme por oido','Uso del BPM y sincronizacion','Cueing y pre-escucha','Transiciones basicas: cut y fade','Estructura musical: intro, drop y outro','Construccion de un set de 30 minutos','Seleccion musical y lectura del publico','Practica de presentacion en vivo']],
            ['name' => 'DJ Avanzado',           'description' => 'Tecnicas avanzadas de mezcla.',    'lessons' => ['Repaso de tecnicas basicas','Scratching: tipos y tecnicas avanzadas','Uso de efectos en la mezcladora','Harmonic mixing: mezclado por clave','Remixeo en vivo con loops','Uso de controladores MIDI','Trabajando con stems','Construccion de sets tematicos','Gestion y organizacion de libreria musical','Practica de set 60 minutos en vivo']],
            ['name' => 'Produccion Musical',    'description' => 'Produccion con Ableton Live.',     'lessons' => ['Introduccion a Ableton Live','Sintesis de sonido: osciladores y filtros','Drum programming: patrones y grooves','Basslines y melodias con MIDI','Sampling y manipulacion de audio','Automatizacion y modulaciones','Mezcla: EQ, compresion y efectos','Masterizacion basica','Exportacion y formatos de audio','Produccion de un track completo']],
            ['name' => 'Fundamentos Musicales', 'description' => 'Teoria musical para DJs.',         'lessons' => ['El sonido y sus propiedades','Notas, escalas y tonalidades','Ritmo, compas y tempo','Acordes basicos y progresiones','El circulo de quintas','Estructuras de canciones electronicas','Lectura de una pista y su forma de onda','Oido musical: intervalos y frecuencias','Aplicacion de la teoria al DJing','Practica y evaluacion final']],
        ];

        $created = [];
        foreach ($coursesData as $data) {
            $course = Course::create(['name' => $data['name'], 'description' => $data['description'], 'total_lessons' => count($data['lessons']), 'active' => true]);
            foreach ($data['lessons'] as $i => $title) {
                Lesson::create(['course_id' => $course->id, 'title' => $title, 'description' => null, 'order' => $i + 1]);
            }
            $created[] = $course;
        }

        // Maestros
        $m1 = User::create(['name' => 'DJ Carlos Herrera',  'role' => 'maestro', 'email' => 'maestro@axss.edu',  'phone' => '555-1001', 'password' => Hash::make('password')]);
        $m2 = User::create(['name' => 'Prod. Laura Mendez', 'role' => 'maestro', 'email' => 'maestro2@axss.edu', 'phone' => '555-1002', 'password' => Hash::make('password')]);

        // Alumnos
        $a1 = User::create(['name' => 'Andres Flores',  'role' => 'alumno', 'email' => 'alumno1@axss.edu', 'phone' => '555-2001', 'password' => Hash::make('password')]);
        $a2 = User::create(['name' => 'Sofia Ramirez',  'role' => 'alumno', 'email' => 'alumno2@axss.edu', 'phone' => '555-2002', 'password' => Hash::make('password')]);
        $a3 = User::create(['name' => 'Miguel Torres',  'role' => 'alumno', 'email' => 'alumno3@axss.edu', 'phone' => '555-2003', 'password' => Hash::make('password')]);

        // Inscripciones
        $a1->courses()->attach($created[0]->id, ['current_lesson' => 4]);
        $a1->courses()->attach($created[2]->id, ['current_lesson' => 2]);
        $a2->courses()->attach($created[0]->id, ['current_lesson' => 7]);
        $a2->courses()->attach($created[1]->id, ['current_lesson' => 1]);
        $a3->courses()->attach($created[3]->id, ['current_lesson' => 3]);

        // Horarios DJ Basico
        Schedule::create(['classroom_id'=>$salonA->id,'course_id'=>$created[0]->id,'teacher_id'=>$m1->id,'day_of_week'=>'Lunes',     'start_time'=>'10:00','end_time'=>'11:00','active'=>true]);
        Schedule::create(['classroom_id'=>$salonA->id,'course_id'=>$created[0]->id,'teacher_id'=>$m1->id,'day_of_week'=>'Miércoles', 'start_time'=>'10:00','end_time'=>'11:00','active'=>true]);
        Schedule::create(['classroom_id'=>$salonA->id,'course_id'=>$created[0]->id,'teacher_id'=>$m1->id,'day_of_week'=>'Viernes',   'start_time'=>'16:00','end_time'=>'17:00','active'=>true]);
        // Horarios DJ Avanzado
        Schedule::create(['classroom_id'=>$salonB->id,'course_id'=>$created[1]->id,'teacher_id'=>$m1->id,'day_of_week'=>'Martes',    'start_time'=>'09:00','end_time'=>'10:00','active'=>true]);
        Schedule::create(['classroom_id'=>$salonB->id,'course_id'=>$created[1]->id,'teacher_id'=>$m1->id,'day_of_week'=>'Jueves',    'start_time'=>'09:00','end_time'=>'10:00','active'=>true]);
        // Horarios Produccion
        Schedule::create(['classroom_id'=>$salonB->id,'course_id'=>$created[2]->id,'teacher_id'=>$m2->id,'day_of_week'=>'Lunes',     'start_time'=>'14:00','end_time'=>'15:30','active'=>true]);
        Schedule::create(['classroom_id'=>$salonB->id,'course_id'=>$created[2]->id,'teacher_id'=>$m2->id,'day_of_week'=>'Miércoles', 'start_time'=>'14:00','end_time'=>'15:30','active'=>true]);
        // Horarios Fundamentos
        Schedule::create(['classroom_id'=>$salonA->id,'course_id'=>$created[3]->id,'teacher_id'=>$m2->id,'day_of_week'=>'Sábado',    'start_time'=>'11:00','end_time'=>'12:00','active'=>true]);
    }
}
