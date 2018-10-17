<?php

namespace App\Http\Controllers;

use App\Sby;
use Illuminate\Http\Request;

class JsonController extends Controller
{
    public function index(Request $request){
        if ($request->input('kelurahan') == null && $request->input('kecamatan') == null){
            $datas = Sby::select('*')
                ->selectRaw('asText(SHAPE) as geometry')
                ->get();
        }
        elseif($request->input('kelurahan') != null && ($request->input('kecamatan') == null)) {
            $kelurahan = explode(",",$request->input('kelurahan'));
            $datas = Sby::select('*')
                ->whereIn('name',$kelurahan)
                ->selectRaw('asText(SHAPE) as geometry')
                ->get();
        }
        elseif($request->input('kelurahan') == null && ($request->input('kecamatan') != null)){
            $kecamatan = explode(",",$request->input('kecamatan'));
            $datas = Sby::select('*')
                ->whereIn('kecamatan',$kecamatan)
                ->selectRaw('asText(SHAPE) as geometry')
                ->get();
        }
        else{
            return response()->json('pilih kecamatan atau kelurahan saja');
        }


        $data2 = [
            'type'=> 'FeatureCollection',
            'name'=> 'Shape of Surabaya',
            'crs' => [],
            'features' =>[],
        ];
        $data2['crs']=[
            'type' => 'name',
            'properties' =>[
                'supported by' => 'https://apollo16team.com',
                'project by' => 'kojyou-project'
            ],
        ];
        foreach ($datas as $key=> $data1) {
            $data2['features'][$key] = [
                'type' => 'Feature',
                'properties'=>[
                    'Name'=>$data1->name,
                    'Kecamatan'=>$data1->kecamatan,
                    'Desa'=>$data1->desa,
                    'origin geometry' => $data1['geometry'],
                ],
                'geometry'=>[
                    'type'=> 'MultiPolygon',
                    'coordinates'=>json_decode(str_replace([',',' ','((','))','POLYGON'], ['],[',',','[[[[',']]]]',''], $data1['geometry'])),
                ],
            ];
        }
        return response()->json($data2);

    }
}
