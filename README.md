# query_builder (Documentacion 1.0)

Clase para construir queries sencillos dinámicos con php y mysql, la clase deriva de otra clase donde se encuentra la cadena de conexion, en este caso esa clase se llama **mainApp** ya la variable que guardar la cadena de conexion es **\$db**. Para la consultas a la base de datos se utiliza **PDO**.

###Funciones

1. insert()
2. update()
3. select()
4. where()
5. bulk()
6. having()
7. checks()

### Uso

Para poder hacer uso de la clase debemos hacer una instancia en el archivo php que vamos a usarla.

```
$queryBuilder = new QueryBuilder();
```

#### 1. insert( dbName,tableName, data, lastId)

La funcion insert se encarga de contruir un query para hacer un insert utilizando _statements_ con PDO para ello es necesario asignarle los siguientes parametros:

| Parametro |  Tipo   |                                                            Descripcion |
| --------- | :-----: | ---------------------------------------------------------------------: |
| dbName    | string  |                                             Nombre de la base de datos |
| tableName | string  |                                                     Nombre de la tabla |
| data      |  array  |                                       arreglo con los datos a insertar |
| lastId    | boolean | true o false, si queremos retornar el ultimo id del registro insertado |

#####Ejemplo

```
$data = array(
    'item' => 'Taladro',
    'cantidad' => 2,
    'fecha' => '2020-02-02'
);

$respuesta = $queryBuilder->insert('inventarioDB','insumos',$data,false);
```

El resultado de la invocacion a la funcion daria como resultado la siguiente ejecucion:

```
$query = "INSERT INTO inventarioDB.insumos (item,cantidad,fecha) VALUES(:item,:cantidad,:fecha)";
$queryPrepared = $db->prepare($query);
$queryPrepared->execute(
    array(
        ':item'=>'Taladro,
        ':cantidad'=>2,
        ':fecha'=>'2020-02-02'
    )
);
```

La invocacion a la funcion _insert()_ devuelve una respuesta y se almacena en la variable **\$respuesta**, en caso de ser un insert exitoso respuesta contrenda el siguiente valor

```
 $respuesta['status'] = true;
```

En caso de ser un insert fallido, respuesta contendra dos valores

```
 $respuesta['status'] = false;
 $respuesta['error'] = 'Mensaje del error producido en myqsl';
```
