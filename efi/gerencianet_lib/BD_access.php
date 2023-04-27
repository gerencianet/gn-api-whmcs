<?php

use Illuminate\Database\Capsule\Manager as Capsule;

function selectCob($table, $conditions, $fields, $limits=1)
{
    try {
        $gerencianetData = Capsule::table($table);
        foreach ($conditions as $key => $value) {
            $gerencianetData = $gerencianetData->where($key, $value);
        }
        if($limits == 1)
            $data = $gerencianetData->select($fields)->first();
        else $data = $gerencianetData->select($fields)->get();
        return  json_decode(json_encode($data), true);  

    } catch (\Exception $e) {
        return null;
    }
}

function insertCob($table, $data)
{
    try {
        Capsule::table($table)->insert($data);
    } catch (\Exception $e) {
        die($e->getMessage());
    }
}

function updateCob($table, $conditions, $updateData)
{
    try {
        $gerencianetData = Capsule::table($table);
        foreach ($conditions as $key => $value) {
            $gerencianetData = $gerencianetData->where($key, $value);
        }
        $gerencianetData->update($updateData);
    } catch (\Exception $e) {
        die($e->getMessage());
    } 
}

function deleteCob($table, $conditions)
{
    try {
        $gerencianetData = Capsule::table($table);
        foreach ($conditions as $key => $value) {
            $gerencianetData = $gerencianetData->where($key, $value);
        }
        $gerencianetData->delete();
    } catch (\Exception $e) {
        die($e->getMessage());
    } 
}
