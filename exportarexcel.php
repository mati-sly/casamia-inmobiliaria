<?php


include("setup/config.php");//Instanciar la libreria
//llamamos al archivo principal de config

header('Content-Type:text/csv; charset=latin1');
header('Content-Disposition: attachment; filename="lista_usuarios.xls"');



?>

<table class="table table-hover">
                    <thead>
                    <tr>
                <!--    <th>gestor o propietario</th>  -->
                        <th>N°</th>
                        
                        <th>Rut</th>
                        <th>Nombre</th>
                        <th>Apellidos</th>
                <!--    <th>Fecha de Nacimiento</th> -->
                        <th>Correo Electrónico</th>
                        <th>Estado</th>
                        
                <!--    <th>Sexo</th> -->
                <!--    <th>Teléfono Móvil</th>  -->
                <!--    <th>Certificado de Antecedentes(Gestor inmobiliario)</th>
                        <th>N° de la propiedad (Dueño inmueble o propietario)</th> -->
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                        $num=1;
                        $sql="select * from usuarios";
                        $result=mysqli_query(conectar(),$sql);
                        while($datos=mysqli_fetch_array($result))
                        {
                    ?>

                        <tr>
                            
                            <td> <?php  echo $num;?> </td>
                           
                            <td> <?php  echo $datos['rut'];?> </td>
                            <td> <?php  echo $datos['nombres'];?> </td>
                            <td> <?php  echo $datos['apellidoPaterno']." ".$datos['apellidoMaterno'];?> </td>
                            <td> <?php  echo $datos['usuario'];?> </td>
                            <td>
                                <?php
                                if($datos['estado']==1)
                                {
                                ?>
                                    <img src="IMG/check.png" width="25px">
                                <?php
                                }else{
                                ?>
                                    <img src="IMG/x.png" width="25px">
                                <?php
                                }
                                ?>
                
                            </td>
                            
                        </tr> 
                    <?php
                        $num++;

                        }
                    ?>


                    </tbody>
                </table>