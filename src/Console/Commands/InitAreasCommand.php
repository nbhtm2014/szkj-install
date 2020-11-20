<?php
/**
 * Creator htm
 * Created by 2020/11/20 16:46
 **/

namespace Szkj\Install\Console\Commands;


use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class InitAreasCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'szkj:init_areas';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Init Areas Data';


    /**
     * @return string
     */
    public function getConnection(): string
    {
        return config('database.default');
    }

    public function __construct()
    {
        parent::__construct();
    }


    public function handle()
    {
        $cline = new Client();
        $rep = $cline->get('http://sz.console.service_0.prevnext.top/query/dict-tree-details/pcd-zh?full=true');
        if ($rep->getStatusCode() == 200){
            $body = json_decode($rep->getBody());
            if (!empty($body->data->define) && $body->code ==200){
                $data = $body->data->children;
                $this->createArea($data);
            }
        }
    }

    public function createArea($data,$name = null){
        foreach ($data as $k => $v){
            $create['name'] = $v->label;
            $create['tag'] = $v->name;
            $create['created_at'] = now();
            $create['updated_at'] = now();
            if ($name){
                $create['pid'] = DB::table('areas')->where('name',$name)->first()->id;
            }
            DB::table('areas')->insert($create);
            if (!empty($v->children)){
                $this->createArea($v->children,$v->label);
            }
        }
    }
}