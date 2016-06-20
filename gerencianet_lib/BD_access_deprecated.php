<?php   

function select($table, $conditions, $fields, $limits=1, $sort='id')
{
    if(is_array($fields))
        $fields      = implode(', ', $fields);

    $sortorder   = "ASC";

    if($limits == 0)
        $limits = "";
    else $limits = (string)$limits;

    $result      = select_query($table, $fields, $conditions, $sort, $sortorder, $limits);

    $response = array();
    if($limits != "1")
    {
        while ($data = mysql_fetch_array($result, MYSQL_ASSOC))
            array_push($response, $data);
    }
    else $response = mysql_fetch_array($result, MYSQL_ASSOC);

    return $response;
}

function insert($table, $data)
{
   insert_query($table, $data);
}

function update($table, $conditions, $updateData)
{
    update_query($table, $updateData, $conditions);
}


?>