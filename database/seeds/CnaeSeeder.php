<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class CnaeSeeder extends Seeder{

    public function run(){
        $request = Http::get('https://servicodados.ibge.gov.br/api/v2/cnae/secoes');
        $sessions = json_decode($request->body(),false);
        foreach($sessions as $session){
            $sessionId = DB::table('cnaes')->insertGetId(
                [
                    'parent_id' => null,
                    'identification'=>$session->id,
                    'name' => $session->descricao,
                ]
            );
            $this->command->info('[Sessão] ID: '.$session->id);

            $request = Http::get('https://servicodados.ibge.gov.br/api/v2/cnae/secoes/'.$session->id.'/divisoes');
            $divisions = json_decode($request->body(),false);

            foreach($divisions as $division):
                $divisionId = DB::table('cnaes')->insertGetId(
                    [
                        'parent_id' => $sessionId,
                        'identification'=>$division->id,
                        'name' => $division->descricao,
                    ]
                );
                $this->command->info('      [Divisão] ID: '.$division->id);

                $request = Http::get('https://servicodados.ibge.gov.br/api/v2/cnae/divisoes/'.$division->id.'/grupos');
                $groups = json_decode($request->body(),false);

                foreach($groups as $group):
                    $groupId = DB::table('cnaes')->insertGetId(
                        [
                            'parent_id' => $divisionId,
                            'identification'=>substr_replace($group->id, '.', 2, 0),
                            'name' => $group->descricao,
                        ]
                    );
                    $this->command->info('            [Grupo] ID: '.$group->id);

                    $request = Http::get('https://servicodados.ibge.gov.br/api/v2/cnae/grupos/'.$group->id.'/classes');
                    $classes = json_decode($request->body(),false);

                    foreach($classes as $class):
                        $identification = substr_replace($class->id, '.', 2, 0);
                        $identification = substr_replace($identification, '-', 5, 0);
                        $classId = DB::table('cnaes')->insertGetId(
                            [
                                'parent_id' => $groupId,
                                'identification'=>$identification,
                                'name' => $class->descricao,
                            ]
                        );
                        $this->command->info('                  [Classe] ID: '.$class->id);

                        $request = Http::get('https://servicodados.ibge.gov.br/api/v2/cnae/classes/'.$class->id.'/subclasses');
                        $subclasses = json_decode($request->body(),false);

                        $arraySubClasses = [];
                        foreach($subclasses as $subclass):

                            if(!isset($arraySubClasses[$subclass->id])){
                                $arraySubClasses[$subclass->id] = $subclass->descricao;

                                $identification = substr_replace($subclass->id, '-', 4, 0);
                                $identification = substr_replace($identification, '/', 6, 0);
                                $subclassId = DB::table('cnaes')->insertGetId(
                                    [
                                        'parent_id' => $classId,
                                        'identification'=>$identification,
                                        'name' => $subclass->descricao,
                                    ]
                                );
                                $this->command->info('                        [Sub-Classe] ID: '.$subclass->id);
                            }
                        endforeach;

                    endforeach;

                endforeach;

            endforeach;
        }
    }
}
