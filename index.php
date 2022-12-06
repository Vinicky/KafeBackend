<?php 

require_once("conn.php");


?>

<!DOCTYPE html>
<html>
<head>
<style>
h2 {
  font-family: candara, sans-sefir; 
}
table {
  font-family: candara, sans-serif;
  border-collapse: collapse;
  width: 100%;
}

td, th {
  border: 0.5px solid black;
  text-align: center;
  padding: 8px;
}

tr:nth-child(even) {
  background-color: #6990F2;
}
.button{
  font-family: candara, sans-sefir;
  background-color: #6990F2;
  color: black;
  border-radius: 20px;
  border: none;
  margin-top: 5px;
  padding: 15px;
  text-align: center;
  display: inline-block;
  font-size: 15px;
  cursor: pointer;
}
</style>
</head>
<body>

<?php

$id = 1;
$array_jmen = array();

$mysql = "SELECT people.id as id, name, count(drinks.id) as pocet from people inner join drinks on id_people = people.id left join types on id_types = types.id group by people.id";
    $stmt = $conn ->prepare($mysql);
    if($stmt === false){
        echo "err_db";
        die;
    }

    $stmt -> execute();
    $stmt -> store_result();
    $stmt -> bind_result($id,$name,$pocet);
    echo "<h2 style='text-align:center'>Kdo kolik čeho vypil</h2>";
    echo "<table>";
    echo "<tr>";
    echo "<th style='text-align:center'>Kdo</th><th style='text-align:center'>Kolik káv celkem vypito</th>";
    echo "</tr>";
    while ($stmt ->fetch())
    {
        $array = array("id"=>$id,"name"=>$name);
        array_push($array_jmen,$array);
        ?>
        <tr>
            <td><?php echo $name?></td>
            <td><?php echo $pocet?></td>
        </tr>
        
        <?php
    
    }
    echo "</table>";
    echo '<br><h2 style=text-align:center>Kdo co vypil</h2>';
    ?>
    <form>
        <select name = "id">
        <?php
            for ($i=0; $i < count($array_jmen); $i++) { 
                echo "<option value='".$array_jmen[$i]["id"]."'>".$array_jmen[$i]["name"]."</option>";
            }
        ?>
        <br>
        </select>
        <label for="od">Od</label>
        <input type="month" id="od" name="od">
        <label for="do">Do:</label>
        <input type="month" id="do" name="do">
        <button class="button">Vyhledat</button>
    </form>
    <?php
    if(!empty($_GET["od"]) and !empty($_GET["do"]) and !empty($_GET["id"])){
        $cena = 0;
        $od = $_GET["od"] . "-00";
        $do = $_GET["do"] . "-00";
        $id = $_GET["id"];
        echo "Výsledky vyhledávání od: ".$od." do: ".$do;
        $mysql = "SELECT typ, count(drinks.id) as pocet from people inner join drinks on id_people = people.id left join types on id_types = types.id where people.id = ? and date > ? and date < ? group by types.id";
        $stmt = $conn ->prepare($mysql);
        if($stmt === false){
            echo "err_db";
            die;
        }
        $stmt -> bind_param("iss",$id,$od,$do);
        $stmt -> execute();
        $stmt -> store_result();
        $stmt -> bind_result($typ,$pocet);
        echo "<table>";
        echo "<tr>";
        echo "<th style=text-align:center>Co</th><th style=text-align:center>Kolik</th>";
        echo "</tr>";
        while ($stmt ->fetch())
        {
            $cena = $cena + count_all($typ,$pocet)
            ?>
            <tr>
                <td><?php echo $typ?></td>
                <td><?php echo $pocet?></td>
            </tr>
            
            <?php
        
        }
        echo "</table>";
        echo "<p style=text-align:right>Celková útrata je: ". round($cena, 1). "Kč</p>";
    }else{
        echo "Vyberte měsíc/e";
    }
    function count_all($typ,$pocet){
        $cena = 0;
        switch ($typ) {
            case 'Mléko':
                $cena = ($pocet * 40) / 20;
                break;
                
            case 'Espresso':
                $cena = ($pocet * 300) / 142.9;
                break;

            case 'Coffe':
                $cena = ($pocet * 300) / 72.4;
                break;
        
            case 'Long':
                $cena = ($pocet * 300) / 72.4;
                break;
    
            case 'Doppio+':
                $cena = ($pocet * 300) / 47.6;
                break;
            
            default:
                return 0;
                break;
        }
        return $cena;
    }
?>

</body>
</html>