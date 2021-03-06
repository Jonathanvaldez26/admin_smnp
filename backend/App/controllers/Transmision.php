<?php

namespace App\controllers;
//defined("APPPATH") OR die("Access denied");
require_once dirname(__DIR__) . '/../public/librerias/fpdf/fpdf.php';
require_once dirname(__DIR__) . '/../public/librerias/phpqrcode/qrlib.php';


use \Core\View;
use \Core\MasterDom;
use \App\controllers\Contenedor;
use \Core\Controller;
use \App\models\General as GeneralDao;
use \App\models\Usuarios as UsuariosDao;
use \App\models\Cursos as CursosDao;
use \App\models\Transmision as TransmisionDao;

use Generator;

class Transmision extends Controller
{

  private $_contenedor;

  function __construct()
  {
    parent::__construct();
    $this->_contenedor = new Contenedor;
    View::set('header', $this->_contenedor->header());
    View::set('footer', $this->_contenedor->footer());
    // if (Controller::getPermisosUsuario($this->__usuario, "seccion_asistentes", 1) == 0)
    //     header('Location: /Principal/');
  }

  public function index()
  {

    $modalEdit = '';
    foreach (CursosDao::getAllCursos() as $key => $value) {
      $modalEdit .= $this->generarModalEditCursos($value);
    }

    $cursosAll = CursosDao::getAllCursos();
    $optionCursos = '';

    foreach ($cursosAll as $key => $value) {
      $optionCursos .= <<<html
                <option value="{$value['id_curso']}">{$value['nombre']} </option>
html;
    }


    View::set('tabla', $this->getAllTransmision());
    // View::set('tablaUsersCursos',$this->getUsersCourse());
    View::set('tablaUsers', $this->getUsers());
    View::set('asideMenu', $this->_contenedor->asideMenu());
    View::set('modalEdit', $modalEdit);
    View::render("transmision_all");
  }


  public function saveTransmision()
  {
    $data = new \stdClass();

    $nombre = MasterDom::getData('nombre');
    // $fecha_curso = MasterDom::getData('fecha_curso');
    // $horario_transmision = MasterDom::getData('horario_transmision');
    // $pdf_constancia = MasterDom::getData('pdf_constancia');  
    $url = MasterDom::getData('url_curso');
    $duracion = MasterDom::getData('duracion');
    // $descripcion = MasterDom::getData('descripcion');
    // $name_files = $this->generateRandomString();

    $data->_nombre = $nombre;
    $data->_url = $url;
    $data->_duracion = $duracion;

    $id = TransmisionDao::insert($data);
    if ($id) {
      echo 'success';
    } else {
      echo 'fail';
    }
  
  }

  public function saveAsignaCurso()
  {
    $data = new \stdClass();

    $id_curso = MasterDom::getData('id_curso');
    $id_registrado = MasterDom::getData('id_registrado');

    $data->_id_curso = $id_curso;
    $data->_id_registrado = $id_registrado;

    $id = CursosDao::insertAsignaCurso($data);
    if ($id) {
      echo 'success';
    } else {
      echo 'fail';
    }
  }

  public function deleteCourseUser()
  {
    $id_ac = $_POST['id_ac'];
    $data = new \stdClass();
    $data->id_asigna_curso = $id_ac;

    $id = CursosDao::UpdateStatusAsignaCurso($data);

    if ($id) {
      echo 'success';
    } else {
      echo 'fail';
    }
  }

  public function getCursosNotInUser()
  {
    $id_user = $_POST['id_registrado'];
    if (isset($id_user)) {
      $Cursos = CursosDao::getCoursesNotSelectByUser($id_user);

      echo json_encode($Cursos);
    }
  }


  //Metodo para reaslizar busqueda de usuarios, sin este metodo no podemos obtener informacion en la vista
  public function Usuario()
  {
    $search = $_POST['search'];

    // $all_ra = AsistentesDao::getAllRegistrosAcceso();
    // $this->setTicketVirtual($all_ra);
    // $this->setClaveRA($all_ra);

    $modalEdit = '';
    foreach (GeneralDao::getAllColaboradoresByName($search) as $key => $value) {
      $modalEdit .= $this->generarModalEditCursos($value);
    }



    $especialidades = EspecialidadesDao::getAll();
    $optionEspecialidad = '';

    foreach ($especialidades as $key => $value) {
      $optionEspecialidad .= <<<html
                <option value="{$value['nombre']}">{$value['nombre']}</option>
html;
    }

    $paises = UsuariosDao::getPais();
    $optionPais = '';
    foreach ($paises as $key => $value) {
      $optionPais .= <<<html
                    <option value="{$value['id_pais']}">{$value['pais']}</option>
html;
    }



    View::set('asideMenu', $this->_contenedor->asideMenu());
    View::set('optionEspecialidad', $optionEspecialidad);
    View::set('optionPais', $optionPais);
    View::set('tabla', $this->getAllTransmision());
    View::set('modalEdit', $modalEdit);
    View::render("usuarios_all");
  }

  public function saveData()
  {
    $data = new \stdClass();
    $data->_nombre = MasterDom::getData('nombre');
    $data->_apellidop = MasterDom::getData('apellidop');
    $data->_apellidom = MasterDom::getData('apellidom');
    $data->_email = MasterDom::getData('email');
    $data->_prefijo = MasterDom::getData('prefijo');
    $data->_especialidad = MasterDom::getData('especialidad');
    $data->_telefono = MasterDom::getData('telefono');
    $data->_pais = MasterDom::getData('pais');
    $data->_estado = MasterDom::getData('estado');
    // $data->_utilerias_administrador_id = $_SESSION['utilerias_administradores_id'];

    $id = UsuariosDao::insert($data);
    if ($id >= 1) {
      echo "success";
      // $this->alerta($id,'add');
      //header('Location: /PickUp');
    } else {
      echo "error";
      // header('Location: /PickUp');
      //var_dump($id);
    }
  }

  public function updateData()
  {
    $data = new \stdClass();
    $data->_id_curso = MasterDom::getData('id_curso');
    $data->_nombre = MasterDom::getData('nombre');
    $data->_fecha_curso = MasterDom::getData('fecha_curso');
    $data->_horario_transmision = MasterDom::getData('horario_transmision');
    $data->_id_modalidad = MasterDom::getData('id_modalidad');
    $data->_url_curso = MasterDom::getData('url_curso');
    $data->_duracion = MasterDom::getData('duracion');
    $data->_descripcion = MasterDom::getData('descripcion');

    // $data->_utilerias_administrador_id = $_SESSION['utilerias_administradores_id'];

    $id = CursosDao::update($data);

    // var_dump($id);
    if ($id) {
      echo "success";
      // $this->alerta($id,'add');
      //header('Location: /PickUp');
    } else {
      echo "error";
      // header('Location: /PickUp');
      //var_dump($id);
    }
  }

  public function isUserValidate()
  {
    echo (count(UsuariosDao::getUserRegister($_POST['email'])) >= 1) ? 'true' : 'false';
  }

  public function setTicketVirtual($asistentes)
  {
    foreach ($asistentes as $key => $value) {
      if ($value['clave'] == '' || $value['clave'] == NULL || $value['clave'] == 'NULL') {
        $clave_10 = $this->generateRandomString(6);
        AsistentesDao::updateTicketVirtualRA($value['id_registro_acceso'], $clave_10);
      }
    }
  }

  public function setClaveRA($all_ra)
  {
    foreach ($all_ra as $key => $value) {
      if ($value['clave'] == '' || $value['clave'] == NULL || $value['clave'] == 'NULL') {
        $clave_10 = $this->generateRandomString(10);
        AsistentesDao::updateClaveRA($value['id_registro_acceso'], $clave_10);
      }
    }
  }



  public function generaterQr($clave_ticket)
  {

    $codigo_rand = $clave_ticket;

    $config = array(
      'ecc' => 'H',    // L-smallest, M, Q, H-best
      'size' => 11,    // 1-50
      'dest_file' => '../public/qrs/' . $codigo_rand . '.png',
      'quality' => 90,
      'logo' => 'logo.jpg',
      'logo_size' => 100,
      'logo_outline_size' => 20,
      'logo_outline_color' => '#FFFF00',
      'logo_radius' => 15,
      'logo_opacity' => 100,
    );

    // Contenido del c??digo QR
    $data = $codigo_rand;

    // Crea una clase de c??digo QR
    $oPHPQRCode = new PHPQRCode();

    // establecer configuraci??n
    $oPHPQRCode->set_config($config);

    // Crea un c??digo QR
    $qrcode = $oPHPQRCode->generate($data);

    //   $url = explode('/', $qrcode );
  }

  public function Actualizar()
  {

    $documento = new \stdClass();

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {

      $id_registro = $_POST['id_registro'];
      $nombre = $_POST['nombre'];
      $segundo_nombre = $_POST['segundo_nombre'];
      $apellido_paterno = $_POST['apellido_paterno'];
      $apellido_materno = $_POST['apellido_materno'];
      $fecha_nacimiento = $_POST['fecha_nacimiento'];
      $email = $_POST['email'];
      $telefono = $_POST['telefono'];
      // $alergias = $_POST['select_alergico'];
      // $alergias_otro = $_POST['alergias_otro'];
      $alergia_medicamento = $_POST['confirm_alergia'];
      if (isset($_POST['alergia_medicamento_cual'])) {
        $alergia_medicamento_cual = $_POST['alergia_medicamento_cual'];
      } else {
        $alergia_medicamento_cual = '';
      }
      $alergia_medicamento_cual = $_POST['alergia_medicamento_cual'];
      $restricciones_alimenticias = $_POST['restricciones_alimenticias'];
      $restricciones_alimenticias_cual = $_POST['restricciones_alimenticias_cual'];

      $documento->_nombre = $nombre;
      $documento->_segundo_nombre = $segundo_nombre;
      $documento->_apellido_paterno = $apellido_paterno;
      $documento->_apellido_materno = $apellido_materno;
      $documento->_fecha_nacimiento = $fecha_nacimiento;
      $documento->_email = $email;
      $documento->_telefono = $telefono;
      // $documento->_alergias = $alergias;
      // $documento->_alergias_otro = $alergias_otro;
      // $documento->_alergia_medicamento = $alergia_medicamento;
      // $documento->_alergia_medicamento_cual = $alergia_medicamento_cual;
      $documento->_restricciones_alimenticias = $restricciones_alimenticias;
      $documento->_restricciones_alimenticias_cual = $restricciones_alimenticias_cual;

      // var_dump($documento);
      $id = AsistentesDao::update($documento);

      if ($id) {
        echo "success";
      } else {
        echo "fail";
        // header("Location: /Home/");
      }
    } else {
      echo 'fail REQUEST';
    }
  }

  public function darClaveRegistrosAcceso($id, $clave)
  {
    AsistentesDao::updateClaveRA($id, $clave);
  }

  public function generarClave($email)
  {

    // $clave_user = AsistentesDao::getClaveByEmail($email)[0]['clave'];
    $tiene_ticket = AsistentesDao::getClaveByEmail($email)[0]['clave_ticket'];
    $tiene_clave = '';
    $clave_random = $this->generateRandomString(6);
    $id_registros_acceso = AsistentesDao::getRegistroByEmail($email)[0]['id_registro_acceso'];


    if ($tiene_ticket == NULL || $tiene_ticket == 'NULL' || $tiene_ticket == 0) {
      $tiene_clave = 'no_tiene';
      AsistentesDao::insertTicket($clave_random);
      $id_tv = AsistentesDao::getIdTicket($clave_random)[0]['id_ticket_virtual'];
      $asignar_clave = AsistentesDao::generateCodeOnTable($email, $id_tv);
    } else {
      $tiene_clave = 'ya_tiene';
      $asignar_clave = 1;
    }

    if ($asignar_clave) {
      $data = [
        'status' => 'success',
        'tiene_ticket' => $tiene_ticket,
        'clave' => $tiene_clave,
        // 'id_registros_acceso'=>$id_registros_acceso
      ];
    } else {
      $data = [
        'status' => 'fail'
      ];
    }

    echo json_encode($data);
  }



  public function getAllTransmision()
  {

    $html = "";
    foreach (TransmisionDao::getAll() as $key => $value) {


      $optionsStatus = '';


      $estatus = '';
      if ($value['status'] == 0) {

        $optionsStatus .= <<<html

                <option value="0" selected>Inactivo</option>
                <option value="1">Activo sin transmision</option>
                <option value="2">Activo en transmision</option>
html;

        $estatus .= <<<html
                
                <span class="badge badge-danger">Inactivo</span>
html;
      } elseif ($value['status'] == 1) {
        $optionsStatus .= <<<html

                <option value="0">Inactivo</option>
                <option value="1" selected>Activo sin transmision</option>
                <option value="2">Activo en transmision</option>
html;
        $estatus .= <<<html
                
                <span class="badge badge-warning">Activo sin trasmisi??n</span>
html;
      } else {

        $optionsStatus .= <<<html

                <option value="0">Inactivo</option>
                <option value="1">Activo sin transmision</option>
                <option value="2" selected>Activo en transmision</option>
html;

        $estatus .= <<<html
                <span class="badge badge-success">Activo en transmisi??n</span>
html;
      }


      $html .= <<<html
            <tr>
                <td>
                    <div class="d-flex px-3 py-1">
                        
                        <div class="d-flex flex-column justify-content-center text-black">
                    
                            
                                <h6 class="mb-0 text-sm text-move text-black">
                                    <span class="fas fa-play" style="font-size: 13px"></span> {$value['nombre']} - {$estatus}
                                </h6>
                        </div>
                    </div>
                </td>
         
                <td style="text-align:left; vertical-align:middle;"> 
                    
                <div class="d-flex flex-column justify-content-center text-black">                    
                                    
                        <h6 class="mb-0 text-sm  text-black">                                                      
                            <p>URL : <a href="$value[url]" target="blank_">{$value['url']}</a></p>                              
                            <p>Duraci??n : {$value['duracion']}</p>                                                     
                        </h6>
                </div>
                <hr>
                </td>

                <td>
                    <div class="d-flex  justify-content-center text-black">
                     <button class="btn bg-gradient-primary mb-0 btn-icon-only" type="button" title="Editar Usuario" data-toggle="modal" data-target="#editar-curso{$value['id_curso']}"><i class="fa fa-edit" aria-hidden="true"></i></button>
                     
                     </div>
                </td>

                <td>
                    <div class="d-flex  justify-content-center text-black">
                      <select class="form-control change_status" style="width: 150px;" data-id-curso="{$value['id_curso']}">
                        {$optionsStatus}
                      </select>
                     </div> 
                </td>
        </tr>
html;
    }

    return $html;
  }

  public function getUsersCourse()
  {

    $html = "";
    foreach (CursosDao::getAllUsersCourse() as $key => $value) {


      $html .= <<<html
            <tr>
                <td>
                    <div class="d-flex px-3 py-1">
                        
                        <div class="d-flex flex-column justify-content-center text-black">
                    
                            
                                <h6 class="mb-0 text-sm text-move text-black">
                                    <span class="fas fa-user" style="font-size: 13px"></span> {$value['nombre']} - {$value['apellidop']} - {$value['apellidom']}                                    
                                </h6>
                        </div>
                    </div>
                </td>
         
                <td style="text-align:left; vertical-align:middle;"> 
                    
                <div class="d-flex flex-column justify-content-center text-black">                    
                                    
                        <h6 class="mb-0 text-sm  text-black">
                            <p>Nombre : {$value['nombre_curso']} </p>                            
                            <p>Fecha : {$value['horario_transmision']}</p>
                            <p>Hora : {$value['fecha_curso']} </p>                                      
                        </h6>
                </div>
                <hr>
                </td>

                <td>
                <div class="d-flex  justify-content-center text-black">
                     <button class="btn bg-gradient-danger mb-0 btn-icon-only btn_quitar_user_curso" data-id="{$value['id_asigna_curso']}" data-nombre-user="{$value['nombre']} {$value['apellidop']} {$value['apellidom']}" data-nombre-curso="{$value['nombre_curso']}" type="button" title="Quitar Curso"><i class="fa fa-trash" aria-hidden="true"></i></button>                     
                     </div>
                </td>
        </tr>
html;
    }

    return $html;
  }

  public function getUsers()
  {

    $html = "";
    foreach (CursosDao::getAllUsers() as $key => $value) {


      $html .= <<<html
            <tr>
                <td>
                    <div class="d-flex px-3 py-1">
                        
                        <div class="d-flex flex-column justify-content-center text-black">
                    
                                <a href='/Usuarios/Detalles/{$value['clave']}'> 
                                <h6 class="mb-0 text-sm text-move text-black">
                                    <span class="fas fa-user" style="font-size: 13px"></span> {$value['nombre']} - {$value['apellidop']} - {$value['apellidom']}                                    
                                </h6>
                                </a>
                        </div>
                    </div>
                </td>
         
                <td style="text-align:left; vertical-align:middle;"> 
                    
                <div class="d-flex flex-column justify-content-center text-black">                    
                                    
                        <h6 class="mb-0 text-sm  text-black">
                           {$value['fecha_registro']}                               
                        </h6>
                </div>
                
                </td>

                <td>
                <div class="d-flex  justify-content-center text-black">
                  <h6 class="mb-0 text-sm  text-black">
                      {$value['nombre_modalidad']}                               
                  </h6>
                </td>
        </tr>
html;
    }

    return $html;
  }



  public function updateStatusCurso()
  {
    $documento = new \stdClass();
    $status = $_POST['status'];
    $id_curso = $_POST['id_curso'];

    $documento->_status = $status;
    $documento->_id_curso = $id_curso;

    $update = CursosDao::updateStatus($documento);

    if ($update) {
      echo "success";
    } else {
      echo "fail";
    }
  }

  public function generarModalEditCursos($datos)
  {
    $modal = <<<html
            <div class="modal fade" id="editar-curso{$datos['id_curso']}" role="dialog" aria-labelledby="" aria-hidden="true">
                <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">
                        Editar Curso
                    </h5>

                    <span type="button" class="btn bg-gradient-danger" data-dismiss="modal" aria-label="Close">
                        X
                    </span>
                </div>
                <div class="modal-body">
                    <p style="font-size: 12px">A continuaci??n ingrese los datos del curso.</p>
                    <hr>
                    <form method="POST" enctype="multipart/form-data" class="form_datos_edit">
                    <input type="hidden" id="id_curso" name="id_curso" value="{$datos['id_curso']}">
                    <div class="form-group row">
                    <div class="form-group col-md-4">
                        <label class="control-label col-md-12 col-sm-1 col-xs-12" for="nombre">Nombre <span class="required">*</span></label>
                        <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Nombre" value="{$datos['nombre']}"required>
                    </div>

                    <div class="form-group col-md-4">
                        <label class="control-label col-md-12 col-sm-1 col-xs-12" for="fecha_curso">Fecha Curso <span class="required">*</span></label>
                        <input type="date" class="form-control" id="fecha_curso" name="fecha_curso" placeholder="Fecha Curso" value="{$datos['fecha_curso']}" required>
                    </div>

                    <div class="form-group col-md-4">
                        <label class="control-label col-md-12 col-sm-1 col-xs-12" for="horario_transmision">Horario Transmisi??n <span class="required">*</span></label>
                        <input type="time" class="form-control" id="horario_transmision" name="horario_transmision" placeholder="Horario transmisi??n" value="{$datos['horario_transmision']}" required>
                    </div>


                    <div class="form-group col-md-4">
                        <label class="control-label col-md-12 col-sm-1 col-xs-12" for="id_modalidad">Modalidad <span class="required">*</span></label>
                        <select class="multisteps-form__select form-control all_input_select" name="id_modalidad" id="id_modalidad" required>
html;

    foreach (CursosDao::getAllModalidad() as $key => $value) {
      $selectedModalidad = ($value['id_modalidad'] == $datos['id_modalidad']) ? 'selected' : '';
      $modal .= <<<html
                                  <option value="{$value['id_modalidad']}" $selectedModalidad>{$value['nombre']}</option>
                        html;
    }
    $modal .= <<<html
                        </select>
                    </div>


                    <div class="form-group col-md-4">
                        <label class="control-label col-md-12 col-sm-1 col-xs-12" for="url_curso">URL <span class="required">*</span></label>
                        <input type="text" class="form-control" id="url_curso" name="url_curso" placeholder="URL" value={$datos['url']} >
                    </div>

                    <div class="form-group col-md-4">
                        <label class="control-label col-md-12 col-sm-1 col-xs-12" for="duracion">Duraci??n <span class="required">*</span></label>
                        <input type="text" class="form-control" id="duracion" name="duracion" placeholder="Duracion" value={$datos['duracion']} required>
                    </div> 

                    <div class="form-group col-md-12">
                        <label class="control-label col-md-12 col-sm-1 col-xs-12" for="descripcion">Descripci??n <span class="required">*</span></label>
                        <textarea name="descripcion" id="descripcion" class="form-control">{$datos['descripcion']}</textarea>                              
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn bg-gradient-success" id="btn_upload" name="btn_upload">Aceptar</button>
                        <button type="button" class="btn bg-gradient-secondary" data-dismiss="modal">Cancelar</button>
                    </div>
                </div>
                    </form>
                </div>

            </div>
                </div>
            </div>
html;

    return $modal;
  }




  public function getAsistentesFaltantes()
  {

    $html = "";
    foreach (GeneralDao::getAsistentesFaltantes() as $key => $value) {


      $img_user = "/img/user.png";

      $value['apellido_paterno'] = utf8_encode($value['apellido_paterno']);
      $value['apellido_materno'] = utf8_encode($value['apellido_materno']);
      $value['nombre'] = utf8_encode($value['nombre']);



      $html .= <<<html
            <tr>
                <td>                    
                    <h6 class="mb-0 text-sm"><span class="fa fa-user-md" style="font-size: 13px"></span> {$value['nombre']} {$value['segundo_nombre']} {$value['apellido_paterno']} {$value['apellido_materno']}</h6>
                </td>
                <td>
                    <h6 class="mb-0 text-sm"><span class="fa fa-mail-bulk" style="font-size: 13px" aria-hidden="true"></span> {$value['email']}</h6>
                </td>
                <td>
                    <u><a href="https://api.whatsapp.com/send?phone=52{$value['telefono']}&text=Buen%20d%C3%ADa,%20te%20contacto%20de%20parte%20del%20Equipo%20Grupo%20LAHE%20%F0%9F%98%80" target="_blank"><p class="text-sm font-weight-bold text-secondary mb-0"><span class="fa fa-whatsapp" style="font-size: 13px; color:green;"></span> {$value['telefono']}</p></a></u>
                </td>
        </tr>
html;
    }
    return $html;
  }


  function generateRandomString($length = 6)
  {
    return substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $length);
  }

  public function abrirpdf($clave, $noPages = null, $no_habitacion)
  {
    $datos_user = AsistentesDao::getRegistroAccesoByClaveRA($clave)[0];
    $nombre_completo = $datos_user['nombre'] . " " . $datos_user['segundo_nombre'] . " " . $datos_user['apellido_paterno'] . " " . $datos_user['apellido_materno'];
    //$nombre_completo = utf8_decode($_POST['nombre']);
    //$datos_user['numero_habitacion']



    $pdf = new \FPDF($orientation = 'L', $unit = 'mm', array(37, 155));

    for ($i = 1; $i <= $noPages; $i++) {


      $pdf->AddPage();
      $pdf->SetFont('Arial', 'B', 5);    //Letra Arial, negrita (Bold), tam. 20
      $textypos = 5;
      $pdf->setY(2);

      $pdf->Image('https://registro.foromusa.com/assets/pdf/iMAGEN_aso.png', 1, 0, 150, 40);
      $pdf->SetFont('Arial', '', 5);    //Letra Arial, negrita (Bold), tam. 20

      $pdf->SetXY(8.3, 9);
      $pdf->SetFont('Times', 'B', 10);
      #4D9A9B
      $pdf->SetTextColor(0, 0, 0);
      $pdf->Multicell(120, 4.2, $nombre_completo . utf8_decode(" #habitaci??n") . " - " . $no_habitacion, 0, 'C');

      $textypos += 6;
      $pdf->setX(2);

      $textypos += 6;
    }

    $pdf->Output();
  }
}

class PHPQRCode
{ // class start

  /** Configuraci??n predeterminada */
  private $_config = array(
    'ecc' => 'H',                       // Calidad del c??digo QR L-menor, M, Q, H-mejor
    'size' => 15,                       // Tama??o del c??digo QR 1-50
    'dest_file' => '',        // Ruta de c??digo QR creada
    'quality' => 100,                    // Calidad de imagen
    'logo' => '',                       // Ruta del logotipo, vac??o significa que no hay logotipo
    'logo_size' => null,                // tama??o del logotipo, nulo significa que se calcula autom??ticamente de acuerdo con el tama??o del c??digo QR
    'logo_outline_size' => null,        // Tama??o del trazo del logotipo, nulo significa que se calcular?? autom??ticamente de acuerdo con el tama??o del logotipo
    'logo_outline_color' => '#FFFFFF',  // color del trazo del logo
    'logo_opacity' => 100,              // opacidad del logo 0-100
    'logo_radius' => 0,                 // ??ngulo de empalme del logo 0-30
  );


  public function set_config($config)
  {

    // Permitir configurar la configuraci??n
    $config_keys = array_keys($this->_config);

    // Obtenga la configuraci??n entrante y escriba la configuraci??n
    foreach ($config_keys as $k => $v) {
      if (isset($config[$v])) {
        $this->_config[$v] = $config[$v];
      }
    }
  }

  /**
   * Crea un c??digo QR
   * @param    Contenido del c??digo QR String $ data
   * @return String
   */
  public function generate($data)
  {

    // Crea una imagen de c??digo QR temporal
    $tmp_qrcode_file = $this->create_qrcode($data);

    // Combinar la imagen del c??digo QR temporal y la imagen del logotipo
    $this->add_logo($tmp_qrcode_file);

    // Eliminar la imagen del c??digo QR temporal
    if ($tmp_qrcode_file != '' && file_exists($tmp_qrcode_file)) {
      unlink($tmp_qrcode_file);
    }

    return file_exists($this->_config['dest_file']) ? $this->_config['dest_file'] : '';
  }

  /**
   * Crea una imagen de c??digo QR temporal
   * @param    Contenido del c??digo QR String $ data
   * @return String
   */
  private function create_qrcode($data)
  {

    // Imagen de c??digo QR temporal
    $tmp_qrcode_file = dirname(__FILE__) . '/tmp_qrcode_' . time() . mt_rand(100, 999) . '.png';

    // Crea un c??digo QR temporal
    \QRcode::png($data, $tmp_qrcode_file, $this->_config['ecc'], $this->_config['size'], 2);

    // Regresar a la ruta temporal del c??digo QR
    return file_exists($tmp_qrcode_file) ? $tmp_qrcode_file : '';
  }

  /**
   * Combinar im??genes de c??digos QR temporales e im??genes de logotipos
   * @param  String $ tmp_qrcode_file Imagen de c??digo QR temporal
   */
  private function add_logo($tmp_qrcode_file)
  {

    // Crear carpeta de destino
    $this->create_dirs(dirname($this->_config['dest_file']));

    // Obtener el tipo de imagen de destino
    $dest_ext = $this->get_file_ext($this->_config['dest_file']);

    // Necesito agregar logo
    if (file_exists($this->_config['logo'])) {

      // Crear objeto de imagen de c??digo QR temporal
      $tmp_qrcode_img = imagecreatefrompng($tmp_qrcode_file);

      // Obtener el tama??o de la imagen del c??digo QR temporal
      list($qrcode_w, $qrcode_h, $qrcode_type) = getimagesize($tmp_qrcode_file);

      // Obtener el tama??o y el tipo de la imagen del logotipo
      list($logo_w, $logo_h, $logo_type) = getimagesize($this->_config['logo']);

      // Crea un objeto de imagen de logo
      switch ($logo_type) {
        case 1:
          $logo_img = imagecreatefromgif($this->_config['logo']);
          break;
        case 2:
          $logo_img = imagecreatefromjpeg($this->_config['logo']);
          break;
        case 3:
          $logo_img = imagecreatefrompng($this->_config['logo']);
          break;
        default:
          return '';
      }

      // Establezca el tama??o combinado de la imagen del logotipo, si no se establece, se calcular?? autom??ticamente de acuerdo con la proporci??n
      $new_logo_w = isset($this->_config['logo_size']) ? $this->_config['logo_size'] : (int)($qrcode_w / 5);
      $new_logo_h = isset($this->_config['logo_size']) ? $this->_config['logo_size'] : (int)($qrcode_h / 5);

      // Ajusta la imagen del logo seg??n el tama??o establecido
      $new_logo_img = imagecreatetruecolor($new_logo_w, $new_logo_h);
      imagecopyresampled($new_logo_img, $logo_img, 0, 0, 0, 0, $new_logo_w, $new_logo_h, $logo_w, $logo_h);

      // Determinar si se necesita un golpe
      if (!isset($this->_config['logo_outline_size']) || $this->_config['logo_outline_size'] > 0) {
        list($new_logo_img, $new_logo_w, $new_logo_h) = $this->image_outline($new_logo_img);
      }

      // Determine si se necesitan esquinas redondeadas
      if ($this->_config['logo_radius'] > 0) {
        $new_logo_img = $this->image_fillet($new_logo_img);
      }

      // Combinar logotipo y c??digo QR temporal
      $pos_x = ($qrcode_w - $new_logo_w) / 2;
      $pos_y = ($qrcode_h - $new_logo_h) / 2;

      imagealphablending($tmp_qrcode_img, true);

      // Combinar las im??genes y mantener su transparencia
      $dest_img = $this->imagecopymerge_alpha($tmp_qrcode_img, $new_logo_img, $pos_x, $pos_y, 0, 0, $new_logo_w, $new_logo_h, $this->_config['logo_opacity']);

      // Generar imagen
      switch ($dest_ext) {
        case 1:
          imagegif($dest_img, $this->_config['dest_file'], $this->_config['quality']);
          break;
        case 2:
          imagejpeg($dest_img, $this->_config['dest_file'], $this->_config['quality']);
          break;
        case 3:
          imagepng($dest_img, $this->_config['dest_file'], (int)(($this->_config['quality'] - 1) / 10));
          break;
      }

      // No es necesario agregar logo
    } else {

      $dest_img = imagecreatefrompng($tmp_qrcode_file);

      // Generar imagen
      switch ($dest_ext) {
        case 1:
          imagegif($dest_img, $this->_config['dest_file'], $this->_config['quality']);
          break;
        case 2:
          imagejpeg($dest_img, $this->_config['dest_file'], $this->_config['quality']);
          break;
        case 3:
          imagepng($dest_img, $this->_config['dest_file'], (int)(($this->_config['quality'] - 1) / 10));
          break;
      }
    }
  }

  /**
   * Acaricia el objeto de la imagen
   * @param    Objeto de imagen Obj $ img
   * @return Array
   */
  private function image_outline($img)
  {

    // Obtener ancho y alto de la imagen
    $img_w = imagesx($img);
    $img_h = imagesy($img);

    // Calcula el tama??o del trazo, si no est?? configurado, se calcular?? autom??ticamente de acuerdo con la proporci??n
    $bg_w = isset($this->_config['logo_outline_size']) ? intval($img_w + $this->_config['logo_outline_size']) : $img_w + (int)($img_w / 5);
    $bg_h = isset($this->_config['logo_outline_size']) ? intval($img_h + $this->_config['logo_outline_size']) : $img_h + (int)($img_h / 5);

    // Crea un objeto de mapa base
    $bg_img = imagecreatetruecolor($bg_w, $bg_h);

    // Establecer el color del mapa base
    $rgb = $this->hex2rgb($this->_config['logo_outline_color']);
    $bgcolor = imagecolorallocate($bg_img, $rgb['r'], $rgb['g'], $rgb['b']);

    // Rellena el color del mapa base
    imagefill($bg_img, 0, 0, $bgcolor);

    // Combina la imagen y el mapa base para lograr el efecto de trazo
    imagecopy($bg_img, $img, (int)(($bg_w - $img_w) / 2), (int)(($bg_h - $img_h) / 2), 0, 0, $img_w, $img_h);

    $img = $bg_img;

    return array($img, $bg_w, $bg_h);
  }


  private function image_fillet($img)
  {

    // Obtener ancho y alto de la imagen
    $img_w = imagesx($img);
    $img_h = imagesy($img);

    // Crea un objeto de imagen con esquinas redondeadas
    $new_img = imagecreatetruecolor($img_w, $img_h);

    // guarda el canal transparente
    imagesavealpha($new_img, true);

    // Rellena la imagen con esquinas redondeadas
    $bg = imagecolorallocatealpha($new_img, 255, 255, 255, 127);
    imagefill($new_img, 0, 0, $bg);

    // Radio de redondeo
    $r = $this->_config['logo_radius'];

    // Realizar procesamiento de esquinas redondeadas
    for ($x = 0; $x < $img_w; $x++) {
      for ($y = 0; $y < $img_h; $y++) {
        $rgb = imagecolorat($img, $x, $y);

        // No en las cuatro esquinas de la imagen, dibuja directamente
        if (($x >= $r && $x <= ($img_w - $r)) || ($y >= $r && $y <= ($img_h - $r))) {
          imagesetpixel($new_img, $x, $y, $rgb);

          // En las cuatro esquinas de la imagen, elige dibujar
        } else {
          // arriba a la izquierda
          $ox = $r; // centro x coordenada
          $oy = $r; // centro coordenada y
          if ((($x - $ox) * ($x - $ox) + ($y - $oy) * ($y - $oy)) <= ($r * $r)) {
            imagesetpixel($new_img, $x, $y, $rgb);
          }

          // parte superior derecha
          $ox = $img_w - $r; // centro x coordenada
          $oy = $r;        // centro coordenada y
          if ((($x - $ox) * ($x - $ox) + ($y - $oy) * ($y - $oy)) <= ($r * $r)) {
            imagesetpixel($new_img, $x, $y, $rgb);
          }

          // abajo a la izquierda
          $ox = $r;        // centro x coordenada
          $oy = $img_h - $r; // centro coordenada y
          if ((($x - $ox) * ($x - $ox) + ($y - $oy) * ($y - $oy)) <= ($r * $r)) {
            imagesetpixel($new_img, $x, $y, $rgb);
          }

          // abajo a la derecha
          $ox = $img_w - $r; // centro x coordenada
          $oy = $img_h - $r; // centro coordenada y
          if ((($x - $ox) * ($x - $ox) + ($y - $oy) * ($y - $oy)) <= ($r * $r)) {
            imagesetpixel($new_img, $x, $y, $rgb);
          }
        }
      }
    }

    return $new_img;
  }

  // Combinar las im??genes y mantener su transparencia
  private function imagecopymerge_alpha($dest_img, $src_img, $pos_x, $pos_y, $src_x, $src_y, $src_w, $src_h, $opacity)
  {

    $w = imagesx($src_img);
    $h = imagesy($src_img);

    $tmp_img = imagecreatetruecolor($src_w, $src_h);

    imagecopy($tmp_img, $dest_img, 0, 0, $pos_x, $pos_y, $src_w, $src_h);
    imagecopy($tmp_img, $src_img, 0, 0, $src_x, $src_y, $src_w, $src_h);
    imagecopymerge($dest_img, $tmp_img, $pos_x, $pos_y, $src_x, $src_y, $src_w, $src_h, $opacity);

    return $dest_img;
  }


  private function create_dirs($path)
  {

    if (!is_dir($path)) {
      return mkdir($path, 0777, true);
    }

    return true;
  }


  private function hex2rgb($hexcolor)
  {
    $color = str_replace('#', '', $hexcolor);
    if (strlen($color) > 3) {
      $rgb = array(
        'r' => hexdec(substr($color, 0, 2)),
        'g' => hexdec(substr($color, 2, 2)),
        'b' => hexdec(substr($color, 4, 2))
      );
    } else {
      $r = substr($color, 0, 1) . substr($color, 0, 1);
      $g = substr($color, 1, 1) . substr($color, 1, 1);
      $b = substr($color, 2, 1) . substr($color, 2, 1);
      $rgb = array(
        'r' => hexdec($r),
        'g' => hexdec($g),
        'b' => hexdec($b)
      );
    }
    return $rgb;
  }


  private function get_file_ext($file)
  {
    $filename = basename($file);
    list($name, $ext) = explode('.', $filename);

    $ext_type = 0;

    switch (strtolower($ext)) {
      case 'jpg':
      case 'jpeg':
        $ext_type = 2;
        break;
      case 'gif':
        $ext_type = 1;
        break;
      case 'png':
        $ext_type = 3;
        break;
    }

    return $ext_type;
  }
} // class end
