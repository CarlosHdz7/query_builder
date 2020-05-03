<?php
    class QueryBuilder extends mainApp{
        
        function having($queries){
            $query_builded_inner = '';
            if(count($queries) == 0){
                   $query_builded_inner = ' ';
                   return $query_builded_inner;
            }else if(count($queries) == 1){
                   $query_builded_inner .= ' HAVING';
                   $query_builded_inner .= $queries[0];
                   return $query_builded_inner;
            } else if(count($queries) > 1){
                $query_builded_inner .= ' HAVING';
                for ($i=0; $i < count($queries); $i++) { 
                    if($i == 0){
                         $query_builded_inner .= $queries[$i];
                         continue;
                    }
                    $query_builded_inner .= "AND" . $queries[$i];
                }
                return $query_builded_inner;
            }   
        }
        
        function where($queries){
            $query_builded_inner = '';
            if(count($queries) == 0){
                   $query_builded_inner = ' ';
                   return $query_builded_inner;
            }else if(count($queries) == 1){
                   $query_builded_inner .= ' WHERE';
                   $query_builded_inner .= $queries[0];
                   return $query_builded_inner;
            } else if(count($queries) > 1){
                $query_builded_inner .= ' WHERE';
                for ($i=0; $i < count($queries); $i++) { 
                    if($i == 0){
                         $query_builded_inner .= $queries[$i];
                         continue;
                    }
                    $query_builded_inner .= "AND" . $queries[$i];
                }
                return $query_builded_inner;
            }   
        }
        
        function insert($systemdb,$table_name,$data,$lastId){
            $response = array();
            $statements = array();
            $fields = "(";
            $query = "INSERT INTO {$systemdb}.{$table_name} ";
            
            foreach($data as $key => $value){
                $fields .= "{$key},";
            }
            
            $fields = substr($fields, 0, -1);
            $fields .= ") VALUES ( ";
             
            foreach($data as $key => $value){
                $fields .= ":{$key},";
                $statements[":{$key}"] = $value;
            }
            
            $fields = substr($fields, 0, -1);
            $fields .= ")";
            
            $general_query = $query.$fields;
                        
            try{
                $qp = $this->db->prepare($general_query);
                $resp = $qp->execute($statements);
                if($resp){
                    if($lastId == true){
                        $response['lastId'] = $this->db->lastInsertId();
                    }
                    $response['status'] = true;
                    return $response;
                }else{
                    $response['status'] = false;
                    $response['error'] = $resp;
                    return $response;
                }
                
            }catch(Exception $e){
                $response['status'] = false;
                $response['error'] = $e->getMessage();
                return $response;
            }
        }
        
        function update($systemdb,$table_name,$data,$condition){
        
            $statements = array();
            $queries = array();
            
            try{
                $query = "UPDATE {$systemdb}.{$table_name} SET ";
                
                $counter_statement = 1;
                foreach($data as $key => $value){
                    $sets .= "{$key} = :{$key}{$counter_statement},";
                    $statements[":{$key}{$counter_statement}"] = $value;
                    $counter_statement++;
                }
                
                foreach($condition as $key => $value){
                    $queries[] = " {$key} = :{$key}{$counter_statement} ";
                    $statements[":{$key}{$counter_statement}"] = $value;
                    $counter_statement++;
                }
                
                $query_builded = $this->where($queries);
                
                $sets = substr($sets, 0, -1);
    
                $general_query = $query.$sets.$query_builded;
                $qp = $this->db->prepare($general_query);
                $resp = $qp->execute($statements);
                if($resp){
                    $response['status'] = true;
                    return $response;
                }else{
                    $response['status'] = false;
                    return $response;
                }
                
            }catch(Exception $e){
                $response['status'] = false;
                $response['error'] = $e->getMessage();
                return $response;
            }
        }
        
        function select($systemdb,$table_name,$fields,$conditions,$fetchAll = true){
            
            $query_builded = '';
            $fields_text = '';
            
            foreach($fields as $key){
                $fields_text .= "{$key},";
            }
            $fields_text = substr($fields_text, 0, -1);
            
            if(isset($conditions)){
                
                $counter_statement = 1;
                $queries = array();
                $statements = array();
                
                foreach($conditions as $key => $value){
                    $queries[] = " {$key} = :{$key}{$counter_statement} ";
                    $statements[":{$key}{$counter_statement}"] = $value;
                    $counter_statement++;
                }
                
                $query_builded = $this->where($queries);
            }
            
            try{
                $query = "SELECT {$fields_text} FROM {$systemdb}.{$table_name} {$query_builded}";
                
                $qp = $this->db->prepare($query);
                $qp->execute($statements);
                
                if($fetchAll){
                   return $qp->fetchAll(PDO::FETCH_ASSOC); //NOTA: PARAMETRIZAR EL ALL
                }else{
                   return $qp->fetch(PDO::FETCH_ASSOC);
                }
                
            }catch(Exception $e){
                echo $e->getMessage();
            }
        }
        
        function bulk($systemdb,$table_name,$data){
           
            $counter = 0;
            $response = array();
            $statements = array();
            $fields = "";
            $query = "INSERT INTO {$systemdb}.{$table_name} (";
            
            foreach($data[0] as $key => $value){
                $fields .= "{$key},";
            }
            
            $fields = substr($fields, 0, -1);
            $fields .= ") VALUES ";
            
            foreach($data as $array){
                
                $fields .= "(";
                
                foreach($array as $key => $value){
                    $fields .= ":{$key}{$counter},";
                    $statements[":{$key}{$counter}"] = $value;
                }
                $fields = substr($fields, 0, -1);
                $fields .= "),";
                $counter++;
            }
            
            $fields = substr($fields, 0, -1);
            
            $general_query = $query.$fields;
            
            $this->db->beginTransaction();
            
            try{
                $qp = $this->db->prepare($general_query);
                $qp->execute($statements);
                
                $this->db->commit();
                return array('status'=>true);
                
            }catch(Exception $e){
                $this->db->rollback();
                return array('status'=>false,"msg"=>$e->getMessage());
            }
        }
        
        function checks($condition,$arrayChecks,$alias,$column){
            
            $query_estados = '';
            $resp = array();
            
            if(count($arrayChecks) == 1){
                 $query_estados .= " ({$alias}{$column} = :{$column}_term1";
                 $array_term[":{$column}_term1"] = $arrayChecks[0];
            }else if(count($arrayChecks) > 1){
                 $query_estados .= " ({$alias}{$column} = ";
                 for ($i=0; $i < count($arrayChecks); $i++) { 
                      if($i == 0){
                           $query_estados .= ":{$column}_term1";
                           $array_term[":{$column}_term1"] = $arrayChecks[0];
                           continue;
                      }
                      //condition could be AND or OR
                      $query_estados .= " {$condition} {$alias}{$column} = :{$column}_term". ($i + 1);
                      $array_term[":{$column}_term". ($i + 1)] = $arrayChecks[$i];
                 }
            }
            $resp['query'] = $query_estados.') ';
            $resp['array_term'] = $array_term;
            return $resp;
       }
        
    }
?>