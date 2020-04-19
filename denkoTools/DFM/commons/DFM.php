<?php
require_once '../denko/dk.denko.php';
/**
 * Clase estatica que sirve para manejar objetos de gran tamaño y temporales.
 *
 */
define('DFM_DISK', 0);
define('DFM_DB', 1);
define('DFM_SESSION', 2);
define('DFM_DEFAULT_SOURCE_VALUE', getenv('TEMP') . '/' . 'werbung_temp' . '/');
define('NEXTID_FILE_NAME', 'next_id.counter');
define('DFM_DEFAULT_READBYTES', 1048576);

/**
 * Excepción arrojada cuando un método no ha sido implementado en una determianda
 * fuente.
 */
class MethodNotImplementedException extends Exception {

    /**
     * Constructor
     *
     * @param string $methodName Nombre del método que no está implementado
     * @param integer $sourceType Tipo de fuente en la cual el método no está implementado
     */
    public function __construct($methodName, $sourceType) {
        $message = "El método $methodName no tiene implementación para la fuente ";
        
        switch ($sourceType) {
            case DFM_DISK :
                $message .= "disco";
                break;
            case DFM_DB :
                $message .= " base de datos";
                break;
            case DFM_SESSION :
                $message .= "sesión";
                break;
            default :
                $message .= "desconocida";
                break;
        }
        parent::__construct($message, 1);
    }

    public function __toString() {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}

class DFM {
    
    /**
     * Esta es la fuente de donde se guardaran, crearan los objetos.
     * Pueden ser tres posibles valores:
     */
    private static $type_of_source;
    
    /**
     * Un parametro relativo al source. Este parametro tiene diferentes utilidades, dependiendo del
     * source elegido.
     * Si source es
     */
    private static $source;
    
    /**
     * Contiene el valor del siguiente id.
     */
    protected static $next_id;

    #####################################################################################################
    /**
     * Genera el proximo ID. Usado en cuando la fuente es un archivo o en session.
     */
    private static function getNextId() {
        if (! isset(DFM::$next_id)){
            if (file_exists(DFM::$source . NEXTID_FILE_NAME)){
                DFM::$next_id = file_get_contents(DFM::$source . NEXTID_FILE_NAME);
                DFM::$next_id ++;
            
            }else{
                DFM::$next_id = 0;
            }
        }else{
            DFM::$next_id ++;
        }
        file_put_contents(DFM::$source . NEXTID_FILE_NAME, DFM::$next_id);
        return DFM::$next_id;
    }

    #####################################################################################################
    #####################################################################################################
    /**
     * Setea la fuente por defecto que se van a usar en todos los temporales.
     * Para asignarlas usar las constantes:
     * DFM :: DFM_DISK
     * DFM :: DFM_DB
     * DFM :: DFM_SESSION
     */
    static function setSource($aType = DFM_DISK, $aSource = DFM_DEFAULT_SOURCE_VALUE) {
        DFM::$type_of_source = $aType;
        if (isset($aSource) && $aType === DFM_DISK){
            DFM::$source = $aSource . '/';
        }elseif (isset($aSource) && $aType === DFM_DB){
            // Si el tipo es DB y la fuente esta vacia por defecto pongo que
            //use la tabla que se llama temporal.
            if (empty($aSource)){
                $aSource = 'temporal';
            }
            DFM::$source = $aSource;
        }else{
            DFM::$source = $aSource;
        }
    
    }

    #####################################################################################
    #####                               FUNCION GET                                 #####
    #####################################################################################
    /**
     * Retorna el contenido del temporal como un String.
     * En caso de no existir el temporal con id $id o en caso de no estar
     * setead la fuente por defecto retorna NULL.
     */
    static function get($id) {
        switch (DFM::$type_of_source) {
            case DFM_DISK :
                return DFM::get_DISK_implementation(DFM::$source, $id);
                break;
            case DFM_DB :
                return DFM::get_DB_implementation(DFM::$source, $id);
                break;
            case DFM_SESSION :
                return DFM::get_SESSION_implementation(DFM::$source, $id);
                break;
        }
    }

    ####################################################################################################
    private static function get_DISK_implementation(& $source, & $key) {
        //Verificar si el directorio esta creado. Si no crearlo.
        if (! file_exists($source)){
            return null;
        }
        $file_name = DFM::getPath($key);
        if (file_exists($file_name)) return file_get_contents($source . '/' . $file_name);
        else return null;
    }

    ####################################################################################################
    private static function getFileName($key, $ext = 'tmp') {
        return $key . '.' . $ext;
    }

    ####################################################################################################
    private static function get_SESSION_implementation(& $source, & $key) {
        //Verificar si el arreglo existe en sesion, si no crearlo.
        if (! isset($_SESSION [$source])){
            $_SESSION [$source] = array ();
        }
        if (isset($_SESSION [$source] [$key])) return $_SESSION [$source] [$key];
        else return null;
    }

    ####################################################################################################
    private static function get_DB_implementation(& $source, & $id) {
        $dbFile = Denko::daoFactory($source);
        $dbFile instanceof DataObjects_Temporal;
        if (! $dbFile->get($id)){
            return false;
        }
        $data = $dbFile->data;
        unset($dbFile);
        while ( $id ){
            $dbFile = Denko::daoFactory($source);
            $dbFile instanceof DataObjects_Temporal;
            $dbFile->id_temporal_parent = $id;
            if ($dbFile->find(true)){
                $data .= $dbFile->data;
                $id = $dbFile->id_temporal;
            }else{
                $id = null;
            }
            unset($dbFile);
        }
        return $data;
    }

    #####################################################################################
    #####                               FUNCION SET                                 #####
    #####################################################################################
    /**
     * Setea el contenido de un temporal.
     * Esta funcion crea un temporal, le asigna el valor $data y retorna el id del
     * temporal si la operación fue exitosa.
     * Retorna NULL en otro caso.
     */
    static function set($data) {
        switch (DFM::$type_of_source) {
            case DFM_DISK :
                return DFM::set_DISK_implementation(DFM::$source, $data);
                break;
            case DFM_DB :
                return DFM::set_DB_implementation(DFM::$source, $data);
                break;
            case DFM_SESSION :
                return DFM::set_SESSION_implementation(DFM::$source, $data);
                break;
        }
    }

    ####################################################################################################
    private static function set_DISK_implementation(& $source, & $data) {
        //Verificar si el directorio esta creado. Si no crearlo.
        if (! file_exists($source)){
            return null;
        }
        $key = DFM::getNextId();
        $file_name = DFM::getPath($key);
        $flag_file_name = $source . DFM::getFileName($key, 'flag');
        file_put_contents($file_name, $data);
        file_put_contents($flag_file_name, '0');
        return $key;
    }

    ####################################################################################################
    private static function set_DB_implementation(& $source, & $data) {
        $dbFile = Denko::daoFactory($source);
        $dbFile instanceof DataObjects_Temporal;
        $dbFile->flag = 0;
        $dbFile->data = $data;
        return $dbFile->insert();
    }

    ####################################################################################################
    private static function set_SESSION_implementation(& $source, & $data) {
        //Verificar si el arreglo existe en sesion, si no crearlo.
        if (! isset($_SESSION [$source])){
            $_SESSION [$source] = array ();
        }
        $key = DFM::getNextId();
        $_SESSION [$source] [$key] = $data;
        return $key;
    }

    #####################################################################################
    #####                             FUNCION DELETE                                #####
    #####################################################################################
    /**
     * Borra el temporal cuyo id es $id.
     * Retorna TRUE si la operacion fue exitosa; FALSE en otro caso.
     */
    static function delete($id) {
        switch (DFM::$type_of_source) {
            case DFM_DISK :
                return DFM::delete_DISK_implementation(DFM::$source, $id);
                break;
            case DFM_DB :
                return DFM::delete_DB_implementation(DFM::$source, $id);
                break;
            case DFM_SESSION :
                return DFM::delete_SESSION_implementation(DFM::$source, $id);
                break;
        }
    }

    ####################################################################################################
    private static function delete_DISK_implementation(& $source, & $key) {
        //Verificar si el directorio esta creado. Si no crearlo.
        $file_name = DFM::getPath($key);
        $flag_file_name = $source . DFM::getFileName($key, 'flag');
        if (! file_exists($file_name)){
            return TRUE;
        }
        unlink($flag_file_name);
        return unlink($file_name);
    }

    ####################################################################################################
    private static function delete_DB_implementation(& $source, & $id) {
        //borra el temporal y todos sus dependencias de la base.
        $dbFile = Denko::daoFactory($source);
        $dbFile instanceof DataObjects_Temporal;
        
        if (! $dbFile->get($id)){
            return FALSE;
        }
        $dbFile->delete();
        $dbFile->free();
        unset($dbFile);
        while ( $id ){
            $dbFile = Denko::daoFactory($source);
            $dbFile instanceof DataObjects_Temporal;
            $dbFile->id_temporal_parent = $id;
            if ($dbFile->find(true)){
                $id = $dbFile->id_temporal;
                $dbFile->delete();
            }else{
                $id = null;
                return true;
            }
            $dbFile->free();
            unset($dbFile);
        }
        return TRUE;
    }

    ####################################################################################################
    private static function delete_SESSION_implementation(& $source, & $key) {
        //Verificar si el directorio esta creado. Si no crearlo.
        if (! isset($_SESSION [$source])) return TRUE;
        unset($_SESSION [$source] [$key]);
        return TRUE;
    }

    #####################################################################################
    #####                           FUNCION SETFROMFILE                             #####
    #####################################################################################
    /**
     * Setea el contenido del archivo en un temporal.
     * Crea un temporal, le setea el contenido del archivo $fileName en el temporal y
     * retorna el id del temporal creado.
     * El parametro $readBytes especifica de a cuantos bytes se lee del archivo $fileName.
     * Usado para limitar la cantidad de memoria usada en un script php en un determinado
     * momento. Si no se asigna valor al parametro $readBytes toma por defecto 1M.
     */
    static function setFromfile($fileName, $readBytes = DFM_DEFAULT_READBYTES) {
        switch (DFM::$type_of_source) {
            case DFM_DISK :
                return DFM::setFromfile_DISK_implementation(DFM::$source, $fileName, $readBytes);
                break;
            case DFM_DB :
                return DFM::setFromfile_DB_implementation(DFM::$source, $fileName, $readBytes);
                break;
            case DFM_SESSION :
                return DFM::setFromfile_SESSION_implementation(DFM::$source, $fileName, $readBytes);
                break;
        }
    }

    ##################################################################################################
    function setFromFile_DB_implementation($source, $fileName, $readBytes) {
        $fd = fopen($fileName, 'r');
        $parentId = null;
        while ( $datos = fread($fd, $readBytes) ){
            $dbFile = Denko::daoFactory($source);
            $dbFile instanceof DataObjects_Temporal;
            $dbFile->data = $datos;
            $dbFile->id_temporal_parent = $parentId;
            if ($parentId === null){
                $parentId = $returnId = $dbFile->insert();
            }else{
                $parentId = $dbFile->insert();
            }
            unset($dbFile);
            unset($datos);
        }
        fclose($fd);
        return $returnId;
    }

    ##################################################################################################
    function setFromFile_DISK_implementation($source, $fileName, $readBytes) {
        //El parametro $readBYtes aca no se usa, ya que lo que se hace es copiar el archivo.
        //Verificar si el directorio esta creado. Si no crearlo.
        if (! file_exists($source)){
            return null;
        }
        $key = DFM::getNextId();
        $file_name = DFM::getPath($key);
        $flag_file_name = $source . DFM::getFileName($key, 'flag');
        file_put_contents($flag_file_name, '0');
        if (copy($fileName, $file_name)){
            return $key;
        }else{
            return null;
        }
    
    }

    ##################################################################################################
    function setFromFile_SESSION_implementation($source, $fileName, $readBytes) {
        return null;
    }

    #####################################################################################
    #####                               FUNCION DISPLAY                             #####
    #####################################################################################
    /**
     * Hace un display del temporal cuyo id es $id. Basicamente hace un echo del
     * contenido del temporal.
     * El parametro $readBytes especifica de a cuantos bytes se lee del temporal.
     * Usado para limitar la cantidad de memoria usada en un script php en un determinado
     * momento. Si no se asigna valor al parametro $readBytes toma por defecto 1M.
     * Nota: Este parametro es ignorado en la implementacion de base de datos. La
     * cantidad de bytes a ser leidas en la implemnetacion de base de datos esta dada por
     * el tamaño en que fue guardado el temporal.
     */
    static function display($id, $readBytes = DFM_DEFAULT_READBYTES) {
        switch (DFM::$type_of_source) {
            case DFM_DISK :
                return DFM::display_DISK_implementation(DFM::$source, $id, $readBytes);
                break;
            case DFM_DB :
                return DFM::display_DB_implementation(DFM::$source, $id, $readBytes);
                break;
            case DFM_SESSION :
                return DFM::display_SESSION_implementation(DFM::$source, $id, $readBytes);
                break;
        }
    }

    ##################################################################################################
    function display_DB_implementation($source, $id, $readBytes) {
        $dbFile = Denko::daoFactory($source);
        $dbFile instanceof DataObjects_Temporal;
        if (! $dbFile->get($id)){
            return false;
        }
        echo $dbFile->data;
        unset($dbFile);
        
        while ( $id ){
            $dbFile = Denko::daoFactory($source);
            $dbFile instanceof DataObjects_Temporal;
            $dbFile->id_temporal_parent = $id;
            if ($dbFile->find(true)){
                echo $dbFile->data;
                $id = $dbFile->id_temporal;
            }else{
                $id = null;
            }
            unset($dbFile);
        }
    }

    ##################################################################################################
    function display_DISK_implementation($source, $id, $readBytes) {
        if (! file_exists($source)){
            return null;
        }
        $file_name = DFM::getPath($id);
        if (! file_exists($file_name)){
            return null;
        }
        $fd = fopen($file_name, 'r');
        while ( $datos = fread($fd, $readBytes) ){
            echo $datos;
        }
        fclose($fd);
    }

    ##################################################################################################
    function display_SESSION_implementation($source, $fileName, $readBytes) {
        return null;
    }

    #####################################################################################
    #####                               FUNCION GETPATH                             #####
    #####################################################################################
    /**
     * Retorna el path real del temporal.
     * Esta funcion solo se usa en la implementacion de archivos o sesion.
     * Si la fuente es base de datos, esta funcion retornara NULL.
     */
    static function getPath($id) {
        switch (DFM::$type_of_source) {
            case DFM_DISK :
                return DFM::$source . DFM::getFilename($id);
                break;
            case DFM_DB :
                return null;
                break;
            case DFM_SESSION :
                return DFM::$source . '.' . $id;
                break;
        }
    }

    #####################################################################################
    #####                               FUNCION SETFLAG                             #####
    #####################################################################################
    /**
     * Setea el valor del flag del tempoar cuyo id es $id a $flagValue.
     * El flag del temporal es solo para uso externo.
     * Este flag permite etiquetar temporales como de un mismo grupo.
     * Usada para definir estados en los que se puede hallar un temporal.
     * Retorna TRUE en caso de la operacion ser exitosa. FALSE en caso contrario.
     */
    static function setFlag($id, $flagValue) {
        switch (DFM::$type_of_source) {
            case DFM_DISK :
                return DFM::setFlag_DISK_implementation(DFM::$source, $id, $flagValue);
                break;
            case DFM_DB :
                return DFM::setFlag_DB_implementation(DFM::$source, $id, $flagValue);
                break;
            case DFM_SESSION :
                return DFM::setFlag_SESSION_implementation(DFM::$source, $id, $flagValue);
                break;
        }
    }

    ##################################################################################################
    function setFlag_DB_implementation($source, $id, $flagValue) {
        $dbFile = Denko::daoFactory($source);
        $dbFile instanceof DataObjects_Temporal;
        if (! $dbFile->get($id)){
            return FALSE;
        }
        $dbFile->flag = $flagValue;
        $dbFile->update();
        unset($dbFile);
        return TRUE;
    }

    ##################################################################################################
    function setFlag_DISK_implementation($source, $id, $flagValue) {
        if (! file_exists($source)) return FALSE;
        $flag_file_name = $source . DFM::getFileName($id, 'flag');
        file_put_contents($flag_file_name, $flagValue);
        return TRUE;
    }

    ##################################################################################################
    function setFlag_SESSION_implementation($source, $id, $flagValue) {
        return FALSE;
    }

    #####################################################################################
    #####                               FUNCION GETFLAG                             #####
    #####################################################################################
    /**
     * Retorna el valor del flag del temporal cuyo id es $id.
     * En caso de que el temporal no exista, retorna FALSE.
     *
     */
    static function getFlag($id) {
        switch (DFM::$type_of_source) {
            case DFM_DISK :
                return DFM::getFlag_DISK_implementation(DFM::$source, $id);
                break;
            case DFM_DB :
                return DFM::getFlag_DB_implementation(DFM::$source, $id);
                break;
            case DFM_SESSION :
                return DFM::getFlag_SESSION_implementation(DFM::$source, $id);
                break;
        }
    }

    ##################################################################################################
    function getFlag_DB_implementation($source, $id) {
        $dbFile = Denko::daoFactory($source);
        $dbFile instanceof DataObjects_Temporal;
        if (! $dbFile->get($id)){
            return FALSE;
        }
        return $dbFile->flag;
    }

    ##################################################################################################
    function getFlag_DISK_implementation($source, $id) {
        if (! file_exists($source)) return FALSE;
        $flag_file_name = $source . DFM::getFileName($id, 'flag');
        return file_get_contents($flag_file_name);
    }

    ##################################################################################################
    function getFlag_SESSION_implementation($source, $id) {
        return FALSE;
    }

    #####################################################################################
    #####                          FUNCION UPDATEFROMFILE                           #####
    #####################################################################################
    /**
     * Actualiza el valor del temporal cuyo id $id por el contenido del archivo
     * $fileName.
     * El parametro $readBytes especifica de a cuantos bytes se lee del archivo $fileName.
     * Usado para limitar la cantidad de memoria usada en un script php en un determinado
     * momento. Si no se asigna valor al parametro $readBytes toma por defecto 1M.
     */
    static function updateFromfile($id, $fileName, $readBytes = DFM_DEFAULT_READBYTES) {
        switch (DFM::$type_of_source) {
            case DFM_DISK :
                return DFM::updateFromfile_DISK_implementation(DFM::$source, $id, $fileName, $readBytes);
                break;
            case DFM_DB :
                return DFM::updateFromfile_DB_implementation(DFM::$source, $id, $fileName, $readBytes);
                break;
            case DFM_SESSION :
                return DFM::updateFromfile_SESSION_implementation(DFM::$source, $id, $fileName, $readBytes);
                break;
        }
    }

    ##################################################################################################
    function updateFromfile_DB_implementation($source, & $id, $fileName, $readBytes) {
        $dbFile = Denko::daoFactory($source);
        $dbFile instanceof DataObjects_Temporal;
        //Borro los hijos del temporal raiz si existen.
        $dbFile->id_temporal_parent = $id;
        if ($dbFile->find(true)){
            DFM::delete($dbFile->id_temporal);
        }
        unset($dbFile);
        $dbFile = Denko::daoFactory($source);
        $dbFile instanceof DataObjects_Temporal;
        if (! $dbFile->get($id)) return null;
        
        $fd = fopen($fileName, 'r');
        $parentId = null;
        while ( $datos = fread($fd, $readBytes) ){
            $dbFile = Denko::daoFactory($source);
            $dbFile instanceof DataObjects_Temporal;
            $dbFile->data = $datos;
            $dbFile->id_temporal_parent = $parentId;
            if ($parentId === null){
                //Si es el padre, lo actualizo.
                $dbFile->id_temporal = $id;
                $parentId = $returnId = $id;
                $dbFile->update();
            }else{
                //Los demas los creo.
                $parentId = $dbFile->insert();
            }
            unset($dbFile);
            unset($datos);
        }
        fclose($fd);
        return $returnId;
    }

    ##################################################################################################
    function updateFromfile_DISK_implementation($source, & $id, $fileName, $readBytes) {
        if (! file_exists($source)) return false;
        $file_name = DFM::getPath($id);
        if (copy($fileName, $file_name)){
            return $id;
        }else{
            return null;
        }
    }

    ##################################################################################################
    function updateFromfile_SESSION_implementation($source, & $id, $fileName, $readBytes) {
        return null;
    }

    #####################################################################################
    #####                          FUNCION COPYTOFILE                               #####
    #####################################################################################
    /**
     * Guarda el contenido del temporal cuyo id es $id en el archivo $fileName.
     * Retorna TRUE si la operacion pudo ser lleva a cabo, FALSE en otro caso.
     * El archivo generado es una copia del temporal.
     */
    static function copyToFile($id, $fileName) {
        switch (DFM::$type_of_source) {
            case DFM_DISK :
                return DFM::copyToFile_DISK_implementation(DFM::$source, $id, $fileName);
                break;
            case DFM_DB :
                return DFM::copyToFile_DB_implementation(DFM::$source, $id, $fileName);
                break;
            case DFM_SESSION :
                return DFM::copyToFile_SESSION_implementation(DFM::$source, $id, $fileName);
                break;
        }
    }

    ##################################################################################################
    function copyToFile_DB_implementation($source, $id, $fileName) {
        $dbFile = Denko::daoFactory($source);
        $dbFile instanceof DataObjects_Temporal;
        if (! $dbFile->get($id)){
            return FALSE;
        }
        //primero vacio el archivo
        file_put_contents($fileName, '');
        if (! $fd = fopen($fileName, 'a')) return FALSE;
        fwrite($fd, $dbFile->data);
        unset($dbFile);
        while ( $id ){
            $dbFile = Denko::daoFactory($source);
            $dbFile instanceof DataObjects_Temporal;
            $dbFile->id_temporal_parent = $id;
            if ($dbFile->find(true)){
                fwrite($fd, $dbFile->data);
                $id = $dbFile->id_temporal;
            }else{
                $id = null;
            }
            unset($dbFile);
        }
        return TRUE;
    }

    ##################################################################################################
    function copyToFile_DISK_implementation($source, $id, $fileName) {
        if (! file_exists($source)) return FALSE;
        $file_name = DFM::getPath($id);
        if (copy($fileName, $file_name)){
            return TRUE;
        }else{
            return FALSE;
        }
    }

    ##################################################################################################
    function copyToFile_SESSION_implementation($source, $id, $fileName) {
        //FIXME: Implementame
        return FALSE;
    }

    #####################################################################################
    #####                        FUNCION CLONETEMPORAL                              #####
    #####################################################################################
    /**
     * Clona el temporal cuyo id es $id en otro temporal.
     * El parametro $readBytes especifica de a cuantos bytes se lee del temporal.
     * Usado para limitar la cantidad de memoria usada en un script php en un determinado
     * momento. Si no se asigna valor al parametro $readBytes toma por defecto 1M.
     * Nota: Este parametro es ignorado en la implementacion de base de datos. La
     * cantidad de bytes a ser leidas en la implemnetacion de base de datos esta dada por
     * el tamaño en que fue guardado el temporal.
     * Retorna el id del clon del temporal en caso de ser exitosa la clonacion; FALSE en
     * otro caso.
     */
    static function cloneFile($id) {
        switch (DFM::$type_of_source) {
            case DFM_DISK :
                return DFM::setFromFile_DISK_implementation(DFM::$source, DFM::getPath($id), 1048576);
                break;
            case DFM_DB :
                return DFM::cloneFile_DB_implementation(DFM::$source, $id, DFM_DEFAULT_READBYTES);
                break;
            case DFM_SESSION :
                //TODO: Implementame.
                return null;
                break;
        }
    }

    ##################################################################################################
    function cloneFile_DB_implementation($source, $id, $readBytes) {
        $dbFile = Denko::daoFactory($source);
        $dbFile instanceof DataObjects_Temporal;
        if (! $dbFile->get($id)){
            return FALSE;
        }
        $parentId = $dbFile->id_temporal;
        $dbFile->id_temporal = '';
        $clonedParentId = $dbFile->insert();
        $returnId = $clonedParentId;
        unset($dbFile);
        while ( $parentId ){
            $dbFile = Denko::daoFactory($source);
            $dbFile instanceof DataObjects_Temporal;
            $dbFile->id_temporal_parent = $parentId;
            if ($dbFile->find(true)){
                $parentId = $dbFile->id_temporal;
                $dbFile->id_temporal = '';
                $dbFile->id_temporal_parent = $clonedParentId;
                $clonedParentId = $dbFile->insert();
            }else{
                $parentId = null;
            }
            unset($dbFile);
        }
        return $returnId;
    }

    ######################################################################################
    #####                           FUNCION MIGRATE                                  #####
    ######################################################################################
    /**
     * Migra el temporal de la implementacion por defecto a la implementacion
     * especificada por $toImplementation.
     */
    function migrate($toUmplementation) {
        //TODO: Implementame.
        return null;
    }

    #####################################################################################
    #####                      FUNCION GETALLTEMPORALS                              #####
    #####################################################################################
    /**
     * Retorna un arreglo con todos los ids de los temporales almacenados en este momento.
     * Retorna NULL si hubo algun problema con la operacion.
     */
    function getAllFiles($flag = null) {
        switch (DFM::$type_of_source) {
            case DFM_DISK :
                return DFM::getAllFiles_DISK_implementation(DFM::$source);
                break;
            case DFM_DB :
                return DFM::getAllFiles_DB_implementation(DFM::$source, $flag);
                break;
            case DFM_SESSION :
                //TODO: Implementame.
                return NULL;
                break;
        }
        return null;
    }

    ##################################################################################################
    function getAllFiles_DISK_implementation($source) {
        $regexp = '([0-9]*)(\\.tmp)';
        $result = array ();
        $files = scandir($source);
        foreach ( $files as $name => $item ){
            if (ereg($regexp, $item, $matches)){
                $result [] = $matches [1];
            }
        }
        return $result;
    }

    ##################################################################################################
    function getAllFiles_DB_implementation($source, $flag = null) {
        $dbFile = Denko::daoFactory($source);
        $dbFile instanceof DataObjects_Temporal;
        $dbFile->selectAdd();
        $dbFile->flag = $flag;
        $dbFile->selectAdd('id_temporal');
        $dbFile->whereAdd('id_temporal_parent IS NULL');
        $dbFile->find();
        $result = array ();
        while ( $dbFile->fetch() ){
            $result [] = $dbFile->id_temporal;
        }
        return $result;
    }

    #####################################################################################
    #####                      FUNCION PURGETEMPORALSBYFLAG                         #####
    #####################################################################################
    /**
     * Borra todos los temporales cuyo flag sea $flagValue.
     * Retorna la cantidad de temporales borrados.
     */
    function purgeTemporalsByFlag($flagValue = 0) {
        $dbFile_ids = DFM::getAllFiles($flagValue);
        $count = 0;
        foreach ( $dbFile_ids as $name => $id ){
            if (DFM::getFlag($id) == $flagValue){
                DFM::delete($id);
                $count ++;
            }
        }
        return $count;
    }

    #####################################################################################
    #####                              SET NAME                                     #####
    #####################################################################################
    /**
     * Asigna un nombre al archivo.
     *
     * @param integer $id_file El ID del archivo al cual se le quiere asignar el nombre
     * @param string $name El nombre del archivo que se desea asignar al archivo
     *
     * @return boolean true en caso de la operación sea llevada a cabo exitosamente. false
     * en otro caso.
     *
     * @throws MethodNotImplementedException  En caso de no existir la implemetación de ese método
     * para ese tipo de fuente.
     */
    function setName($id_file, $name) {
        switch (DFM::$type_of_source) {
            case DFM_DISK :
                throw new MethodNotImplementedException("setName", DFM::$type_of_source);
                break;
            case DFM_DB :
                return DFM::setName_DB_implementation(DFM::$source, $id_file, $name);
                break;
            case DFM_SESSION :
                throw new MethodNotImplementedException("setName", DFM::$type_of_source);
                break;
        }
        return null;
    }

    ##################################################################################################
    function setName_DB_implementation($source, $id_file, $name) {
        $dbFile = Denko::daoFactory($source);
        $dbFile instanceof DataObjects_Temporal;
        if (! $dbFile->get($id_file)){
            $dbFile->free();
            return false;
        }
        $dbFile->name = $name;
        if ($dbFile->update()){
            $return = true;
        }else{
            $return = false;
        }
        $dbFile->free();
        return $return;
    }

    #####################################################################################
    #####                              SET SIZE                                     #####
    #####################################################################################
    /**
     * Asigna el tamaño en bytes del archivo.
     *
     * @param integer $id_file El ID del archivo al cual se le quiere asignar el tamaño
     * @param integer $size El tamaño del archivo en bytes
     *
     * @return boolean true en caso de la operación sea llevada a cabo exitosamente. false
     * en otro caso.
     *
     * @throws MethodNotImplementedException  En caso de no existir la implemetación de ese método
     * para ese tipo de fuente.
     */
    function setSize($id_file, $size) {
        switch (DFM::$type_of_source) {
            case DFM_DISK :
                throw new MethodNotImplementedException("setSize", DFM::$type_of_source);
                break;
            case DFM_DB :
                return DFM::setSize_DB_implementation(DFM::$source, $id_file, $size);
                break;
            case DFM_SESSION :
                throw new MethodNotImplementedException("setSize", DFM::$type_of_source);
                break;
        }
        return null;
    }

    ##################################################################################################
    function setSize_DB_implementation($source, $id_file, $size) {
        $dbFile = Denko::daoFactory($source);
        $dbFile instanceof DataObjects_Temporal;
        if (! $dbFile->get($id_file)){
            //Si el DAO no existe, retorno false.
            $dbFile->free();
            return false;
        }
        $dbFile->size = $size;
        if ($dbFile->update()){
            $return = true;
        }else{
            $return = false;
        }
        $dbFile->free();
        return $return;
    }

    #####################################################################################
    #####                           SET METADATA                                    #####
    #####################################################################################
    /**
     * Asigna la metadata al archivo.
     *
     * @param integer $id_file El ID del archivo al cual se le quiere asignar la metadata
     * @param array $metadata La metadata del archivo. Consiste de un arreglo clave valor
     *
     * @return boolean true en caso de la operación sea llevada a cabo exitosamente. false
     * en otro caso.
     *
     * @throws MethodNotImplementedException  En caso de no existir la implemetación de ese método
     * para ese tipo de fuente.
     */
    function setMetadata($id_file, $metadata) {
        switch (DFM::$type_of_source) {
            case DFM_DISK :
                throw new MethodNotImplementedException("setMetadata", DFM::$type_of_source);
                break;
            case DFM_DB :
                return DFM::setMetadata_DB_implementation(DFM::$source, $id_file, $metadata);
                break;
            case DFM_SESSION :
                throw new MethodNotImplementedException("setMetadata", DFM::$type_of_source);
                break;
        }
        return null;
    }

    ##################################################################################################
    function setMetadata_DB_implementation($source, $id_file, $metadata) {
        $dbFile = Denko::daoFactory($source);
        $dbFile instanceof DataObjects_Temporal;
        if (! $dbFile->get($id_file)){
            //Si el DAO no existe, retorno false.
            $dbFile->free();
            return false;
        }
        
        //Si están los campos DFMName, DFMSize, o DFMIndex#, los borro.
        if (isset($metadata ['DFMId'])){
            unset($metadata ['DFMId']);
        }
        if (isset($metadata ['DFMName'])){
            unset($metadata ['DFMName']);
        }
        if (isset($metadata ['DFMSize'])){
            unset($metadata ['DFMSize']);
        }
        if (isset($metadata ['DFMIndex1'])){
            unset($metadata ['DFMIndex1']);
        }
        if (isset($metadata ['DFMIndex2'])){
            unset($metadata ['DFMIndex2']);
        }
        if (isset($metadata ['DFMIndex3'])){
            unset($metadata ['DFMIndex3']);
        }
        // Asigno la metadata serializada a JSON.
        $metadata = json_encode($metadata);
        
        $dbFile->metadata = $metadata;
        if ($dbFile->update()){
            $return = true;
        }else{
            $return = false;
        }
        $dbFile->free();
        return $return;
    }

    #####################################################################################
    #####                           GET METADATA                                    #####
    #####################################################################################
    /**
     * Retorna la metadata del archivo cargada por el usuario. Además agrega el nombre
     * (DFMName), el tamaño (DFMSize) y los índices (DFMIndex1, DFMIndex2 y DFMIndex3).
     *
     * @param integer $id_file El ID del archivo al cual se le quiere asignar la metadata
     *
     * @return mixed La metadata del archivo requerida más los campos name, size, index1,
     * index2 e index3 en caso de éxito. false en otro caso.
     *
     * @throws MethodNotImplementedException  En caso de no existir la implemetación de ese método
     * para ese tipo de fuente.
     */
    function getMetadata($id_file) {
        switch (DFM::$type_of_source) {
            case DFM_DISK :
                throw new MethodNotImplementedException("getMetadata", DFM::$type_of_source);
                break;
            case DFM_DB :
                return DFM::getMetadata_DB_implementation(DFM::$source, $id_file);
                break;
            case DFM_SESSION :
                throw new MethodNotImplementedException("getMetadata", DFM::$type_of_source);
                break;
        }
        return null;
    }

    ##################################################################################################
    function getMetadata_DB_implementation($source, $id_file) {
        $dbFile = Denko::daoFactory($source);
        $dbFile instanceof DataObjects_Temporal;
        $dbFile->selectAdd();
        $dbFile->selectAdd('metadata,id_temporal,name,size,index1,index2,index3');
        if (! $dbFile->get($id_file)){
            //Si el DAO no existe, retorno false.
            $dbFile->free();
            return false;
        }
        // Desserializo la metadata.
        $metadata = json_decode($dbFile->metadata, true);
        
        //Agrego los campos correspondientes al archivo.
        $metadata ['DFMId'] = $dbFile->id_temporal;
        $metadata ['DFMName'] = $dbFile->name;
        $metadata ['DFMSize'] = $dbFile->size;
        $metadata ['DFMIndex1'] = $dbFile->index1;
        $metadata ['DFMIndex2'] = $dbFile->index2;
        $metadata ['DFMIndex3'] = $dbFile->index3;
        
        $dbFile->free();
        return $metadata;
    }

    #####################################################################################
    #####                           SET INDEX1                                      #####
    #####################################################################################
    /**
     * Asigna el valor del index1 a un archivo.
     *
     * @param integer $id_file El ID del archivo al cual se le quiere asignar el index1
     * @param integer $index1 El valor que se desea asignar a index1
     *
     * @return boolean true en caso de éxito false en otro caso.
     *
     * @throws MethodNotImplementedException  En caso de no existir la implemetación de ese método
     * para ese tipo de fuente.
     */
    function setIndex1($id_file, $index1) {
        switch (DFM::$type_of_source) {
            case DFM_DISK :
                throw new MethodNotImplementedException("setIndex1", DFM::$type_of_source);
                break;
            case DFM_DB :
                return DFM::setIndex1_DB_implementation(DFM::$source, $id_file, $index1);
                break;
            case DFM_SESSION :
                throw new MethodNotImplementedException("setIndex1", DFM::$type_of_source);
                break;
        }
        return null;
    }

    ##################################################################################################
    function setIndex1_DB_implementation($source, $id_file, $index1) {
        $dbFile = Denko::daoFactory($source);
        $dbFile instanceof DataObjects_Temporal;
        if (! $dbFile->get($id_file)){
            //Si el DAO no existe, retorno false.
            $dbFile->free();
            return false;
        }
        
        $dbFile->index1 = $index1;
        
        if ($dbFile->update()){
            $return = true;
        }else{
            $return = false;
        }
        
        $dbFile->free();
        return $return;
    }

    #####################################################################################
    #####                           SET INDEX2                                      #####
    #####################################################################################
    /**
     * Asigna el valor del index2 a un archivo.
     *
     * @param integer $id_file El ID del archivo al cual se le quiere asignar el index2
     * @param integer $index2 El valor que se desea asignar a index2
     *
     * @return boolean true en caso de éxito false en otro caso.
     *
     * @throws MethodNotImplementedException  En caso de no existir la implemetación de ese método
     * para ese tipo de fuente.
     */
    function setIndex2($id_file, $index2) {
        switch (DFM::$type_of_source) {
            case DFM_DISK :
                throw new MethodNotImplementedException("setIndex2", DFM::$type_of_source);
                break;
            case DFM_DB :
                return DFM::setIndex2_DB_implementation(DFM::$source, $id_file, $index2);
                break;
            case DFM_SESSION :
                throw new MethodNotImplementedException("setIndex2", DFM::$type_of_source);
                break;
        }
        return null;
    }

    ##################################################################################################
    function setIndex2_DB_implementation($source, $id_file, $index2) {
        $dbFile = Denko::daoFactory($source);
        $dbFile instanceof DataObjects_Temporal;
        if (! $dbFile->get($id_file)){
            //Si el DAO no existe, retorno false.
            $dbFile->free();
            return false;
        }
        
        $dbFile->index2 = $index2;
        
        if ($dbFile->update()){
            $return = true;
        }else{
            $return = false;
        }
        
        $dbFile->free();
        return $return;
    }

    #####################################################################################
    #####                           SET INDEX3                                      #####
    #####################################################################################
    /**
     * Asigna el valor del index3 a un archivo.
     *
     * @param integer $id_file El ID del archivo al cual se le quiere asignar el index3
     * @param integer $index3 El valor que se desea asignar a index3
     *
     * @return boolean true en caso de éxito false en otro caso.
     *
     * @throws MethodNotImplementedException  En caso de no existir la implemetación de ese método
     * para ese tipo de fuente.
     */
    function setIndex3($id_file, $index3) {
        switch (DFM::$type_of_source) {
            case DFM_DISK :
                throw new MethodNotImplementedException("setIndex3", DFM::$type_of_source);
                break;
            case DFM_DB :
                return DFM::setIndex3_DB_implementation(DFM::$source, $id_file, $index3);
                break;
            case DFM_SESSION :
                throw new MethodNotImplementedException("setIndex3", DFM::$type_of_source);
                break;
        }
        return null;
    }

    ##################################################################################################
    function setIndex3_DB_implementation($source, $id_file, $index3) {
        $dbFile = Denko::daoFactory($source);
        $dbFile instanceof DataObjects_Temporal;
        if (! $dbFile->get($id_file)){
            //Si el DAO no existe, retorno false.
            $dbFile->free();
            return false;
        }
        
        $dbFile->index3 = $index3;
        
        if ($dbFile->update()){
            $return = true;
        }else{
            $return = false;
        }
        
        $dbFile->free();
        return $return;
    }

    #####################################################################################
    #####                               FIND                                        #####
    #####################################################################################
    /**
     * Busca los archivos según los parámetros pasados.
     *
     * @param integer $index1 El valor del campo $index1 que debe tener el archivo
     * @param integer $index2 El valor del campo $index2 que debe tener el archivo
     * @param integer $index3 El valor del campo $index3 que debe tener el archivo
     *
     * @return mixed Un arreglo de metadatas de los archivos que concuerdan con los
     * parámetros de búsqueda. Si no hay archivos que concuerden con
     * los parámetros de búsqueda, el arreglo será vacío. Retorna false
     * en caso de haber algún error.
     *
     * @throws MethodNotImplementedException  En caso de no existir la implemetación de ese método
     * para ese tipo de fuente.
     */
    function find($index1, $index2 = null, $index3 = null) {
        switch (DFM::$type_of_source) {
            case DFM_DISK :
                throw new MethodNotImplementedException("find", DFM::$type_of_source);
                break;
            case DFM_DB :
                return DFM::find_DB_implementation(DFM::$source, $index1, $index2, $index3);
                break;
            case DFM_SESSION :
                throw new MethodNotImplementedException("find", DFM::$type_of_source);
                break;
        }
        return null;
    }

    ##################################################################################################
    function find_DB_implementation($source, $index1, $index2, $index3) {
        // Al menos un parámetro debe estar asignado
        if ($index1 === null && $index2 === null && $index3 === null){
            return false;
        }
        $dbFile = Denko::daoFactory($source);
        $dbFile instanceof DataObjects_Temporal;
        
        if ($index1 !== null){
            $dbFile->index1 = $index1;
        }
        
        if ($index2 !== null){
            $dbFile->index2 = $index2;
        }
        
        if ($index3 !== null){
            $dbFile->index3 = $index3;
        }
        
        if (! $dbFile->find()){
            $dbFile->free();
            return array ();
        }
        
        $metadatas = array ();
        
        while ( $dbFile->fetch() ){
            $metadatas [] = DFM::getMetadata($dbFile->id_temporal);
        }
        
        return $metadatas;
    
    }
    /*
    #####################################################################################
    #####                              FUNCION NUEVA                                #####
    #####################################################################################
    function NUEVA() {
        switch (DFM :: $type_of_source) {
            case DFM_DISK :
                return DFM :: NUEVA_DISK_implementation( DFM :: $source);
                break;
            case DFM_DB :
                return DFM :: NUEVA_DB_implementation( DFM :: $source);
                break;
            case DFM_SESSION :
                //TODO: Implementame.
                return DFM :: NUEVA_SESSION_implementation( DFM :: $source);
                break;
        }
        return null;
    }
    ##################################################################################################
    function NUEVA_DISK_implementation($source) {
        return FALSE;
    }
    ##################################################################################################
    function NUEVA_DB_implementation($source) {
        return FALSE;
    }
     ##################################################################################################
    function NUEVA_SESSION_implementation($source) {
        return FALSE;
    }
    */
}

class Temporal extends DFM {

}