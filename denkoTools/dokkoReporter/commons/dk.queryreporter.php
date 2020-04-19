<?php
// +----------------------------------------------------------------------+
// |                     DokkoReporter version 0.1                        |
// +----------------------------------------------------------------------+
// |                          2008 Dokko Group                            |
// +----------------------------------------------------------------------+
// |  Copyright (c) 2008 Dokko Group                                      |
// |  Tandil, Buenos Aires, 7000, Argentina                               |
// |  All Rights Reserved.                                                |
// |                                                                      |
// | This software is the confidential and proprietary information of     |
// | Dokko Group. You shall not disclose such Confidential Information    |
// | and shall use it only in accordance with the terms of the license    |
// | agreement you entered into with Dokko Group.                         |
// |                                                                      |
// +----------------------------------------------------------------------+
// | Author: Dokko Group.                                                 |
// +----------------------------------------------------------------------+

require_once '../commons/common.php';
require_once 'MDB2.php';
require_once 'DB/DataObject.php';

/**
* Documentación DokkoReporter 0.1
*
* Herramienta de creación de contenidos web
*
* @author DokkoReporter Developers Group <info@dokkogroup.com.ar>
* @version 0.1
* @package DokkoReporter
* @copyright Copyright (c) 2008 Dokko Group.
**/

class DK_QueryReporter {

    # Producto de la conección con la base de datos.
    /**
     * @var MDB2_Driver_Common
     */
	private $mdb2 = null;

    # Resultados de la ejecución del query en la base de datos.
	private $_dao = null;

    # Propiedades del query.
	private $properties = array();

    # Json especificado en la tabla query tratado como un arreglo.
	private $jsonQueryReport = null;

    # Arreglo encargado de guardar el query.
    private $query = array();

    # Arreglo encargado de guardar los filtros.
    private $filters = array();

    # Id del reporte que se está consultando.
    private $id_report = 0;

    # Valores de los filtros que se aplicarán al query.
    private $filterValues = null;

    # Número de resultados encontrados.
    private $numRows = 0;

    # Número de resultados por página que se deben mostrar.
    private $resultsPerPage = 0;

    //----------------------------------------------------------------------------------

	/**
	 * Constructora
	 */
	public function DK_QueryReporter($id_report, $resultsPerPage = null) {
		$property = PEAR::getStaticProperty('DB_DataObject','options');

        # Realizo la conección.
        $this->mdb2 =& MDB2::connect($property['database']);
        if (PEAR::isError($this->mdb2)) {
            die('<b>MDB2 Error: </b>'.$this->mdb2->getMessage().'<br /><b>MDB2 Debug Info: </b>'.$this->mdb2->getDebugInfo());
        }

        # Define la manera en que traerá la información de la base.
        $this->mdb2->setFetchMode(MDB2_FETCHMODE_OBJECT);

        # Establezco valores de variables.
        $this->id_report = $id_report;
        if ($resultsPerPage !== null && Denko::isInt($resultsPerPage) && $resultsPerPage > 0) {
        	$this->resultsPerPage = $resultsPerPage;
        }
	}

    //----------------------------------------------------------------------------------

	/**
	 * Retorna un arreglo con los objetos resultados del query con id_report = $id.
	 */
	public function find($pageNumber, $filterValues) {

        # Establezco valores de variables.
        $this->filterValues = !empty($filterValues) ? $filterValues : null;  

		# Obtengo el json indicado y lo asigno a la variable de clase como arreglo.
		$query=$this->mdb2->query('SELECT * FROM report WHERE id_report = '.$this->mdb2->quote($this->id_report));
        $query instanceof MDB2_Result_Common;
		$daoQuery = $query->fetchRow();
        $daoQuery instanceof DataObjects_Report;
        # Si no existe el reporte devuelvo null, sino prosigo y establezco el json como
        # arreglo en una variable de clase.
        if ($daoQuery === null) return null;
        $this->jsonQueryReport = json_decode($daoQuery->query_report,true);
        if (!is_array($this->jsonQueryReport) || !isset($this->jsonQueryReport['query'])) {
        	die('<b>DK_QueryReporter Error: </b> el json se encuentra mal constituido.');
        }

        # Establezco los valores del query y de los filtros en las variables de clase.
        $this->query = $this->escapeWildcardsAndQuotes();
        if (isset($this->jsonQueryReport['filters'])) {
            $this->filters = $this->jsonQueryReport['filters'];
            foreach ($this->filters as $filter) {
            	if (!isset($filter['nombre']) || !isset($filter['tipo']) || !isset($filter['default'])) {
            		die('<b>DK_QueryReporter Error: </b> los filtros no están correctamente definidos.');
            	}
            }
        }

        # Me encargo de guardar los valores de los filtros.
        if (isset($this->filterValues)) {
            $this->guardarFiltrosDefault();
        }

        # Seteo configuraciones para la clase.
        $this->setNumRows();
		$this->setLimitToQuery($pageNumber);
		$this->setDao();
        $this->setProperties();

        # Guardo dentro de una variable global el objeto en cuestión para luego poder 
        # recuperarlo en otros bloques o funciones.
        $GLOBALS['DK_REPORTER'] = $this;

        # Retorno el número de tuplas encontradas.
		return $this->numRows;
	}

    //----------------------------------------------------------------------------------

	/**
	 * Funciona como el fetch del DB_DataObject con la salvedad que no queda 
	 * en el dao el valor, sino que se retorna como resultado de la función.
	 */
	public function fetch() {
	    if ($this->_dao) {
	      	$daotupla = $this->_dao->fetchRow();
			foreach($this->properties as $key => $value) {
				@$this->$key = $daotupla->$key;
			}
            return $daotupla;
	    }
	    return null;
	}

    //----------------------------------------------------------------------------------

    /**
     * Obtengo el objeto ya formado y guardado dentro de la variable global.
     * @param Smarty $smarty
     * @return DK_QueryReporter
     */
    public function getDaoLister(&$smarty){
        return $GLOBALS['DK_REPORTER'];
    }

    //----------------------------------------------------------------------------------

    private function __set($property_name, $val) {
        $this->properties[$property_name] = $val;
    }

    //----------------------------------------------------------------------------------

    private function __get($property_name) {
        if (isset($this->properties[$property_name])) {
            return $this->properties[$property_name];
        } else {
            return null;
        }
    }

    //----------------------------------------------------------------------------------

    /**
     * Prepare and excecute the query.
     */
    private function setDao() {

        # Prepare the statement.
        $sth=$this->mdb2->prepare($this->query);
        $sth instanceof MDB2_Statement_Common;
        if (PEAR::isError($sth)) {
            $sth instanceof PEAR_Error;
            die('<b>MDB2 Error: </b>'.$sth->getMessage().'<br /><b>MDB2 Debug Info: </b>'.$sth->getDebugInfo());
        }

        # Execute the query.
        $arraydata  = $this->getFilterValues();
        $this->_dao = $sth->execute($arraydata);
        if (PEAR::isError($this->_dao)) {
            die('<b>MDB2 Error: </b>'.$this->_dao->getMessage().'<br /><b>MDB2 Debug Info: </b>'.$this->_dao->getDebugInfo());
        }

        # Freeing memory.
        $sth->free();
    }

    //----------------------------------------------------------------------------------

    /**
     * Carga las propiedades dinamicamente en la clase para poder accederlas como si 
     * fuera un objeto.
     */
    private function setProperties() {
        if ($this->_dao !== null) {
            foreach ($this->_dao->getColumnNames() as $name => $column) {
                $this->$name = 'vacio';
            }
        }
    }

    //----------------------------------------------------------------------------------

	/**
	 * Devuelve un arreglo con los nombre de las propiedades del query realizado.
	 */
	public function getProperties() {
	    return array_keys($this->properties);
	}

    //----------------------------------------------------------------------------------

	/**
	 * Define el limite de busqueda al query del reporte.
	 */
	private function setLimitToQuery($pageNumber) {
		$offset   = ($pageNumber > 1) ? $this->resultsPerPage*($pageNumber-1) : 0;
		$rowcount = ($this->resultsPerPage === 0) ? 0 : $this->resultsPerPage;
	    if (isset($this->query) && !empty($rowcount)) {
	        if (substr_count($this->query,';')) {
                $this->query = str_replace(';',' LIMIT '.$offset.','.$rowcount.';', $this->query);
            } else {
            	$this->query .= ' LIMIT '.$offset.','.$rowcount.';';
            }
	    }
	}

    //----------------------------------------------------------------------------------

    /**
     * Preparo el query, escapando wildcards y quotes para que se produzca un error de
     * sintaxis al realizar el prepare.
     */
    private function escapeWildcardsAndQuotes() {
        $queryArray = explode('?',$this->jsonQueryReport['query']);

        # Extraigo los comodines (% o _) que se apliquen a los filtros (?) del query.
        foreach ($queryArray as $key => $value) {
            if (substr($value,0,1) == '%' || substr($value,0,1) == '_') {
                $value = substr($value,1);
            }
            if (substr($value,-1) == '%' || substr($value,-1) == '_') {
                $value = substr($value,0,strlen($value)-1);
            }
            $queryArray[$key] = $value;
        }

        # Extraigo las comillas (' o ") que se apliquen a los filtros (?) del query.
        foreach ($queryArray as $key => $value) {
            if (substr($value,0,1) == '\'' || substr($value,0,1) == '"') {
                $value = substr($value,1);
            }
            if (substr($value,-1) == '\'' || substr($value,-1) == '"') {
                $value = substr($value,0,strlen($value)-1);
            }
            $queryArray[$key] = $value;
        }

        # Retorno el query en perfectas condiciones para hacer el prepare.
        return implode('?',$queryArray);
    }

    //----------------------------------------------------------------------------------
    //------------------------------------ FILTROS -------------------------------------
    //----------------------------------------------------------------------------------

    /**
     * Retorna el número de filtros que posee el query.
     */
    public function getCantFilters() {
    	return count($this->filters);
    }

    //----------------------------------------------------------------------------------

    /**
     * Obtiene el valor del filtro que se encuentra dentro de la tabla configuración. Si
     * este no existiese o es vacío, entrega el valor por defecto previamente seteado.
     */
    public function getFilterValue($id_filtro) {
        $query = $this->mdb2->query('SELECT valor FROM `configuracion` WHERE indice1 = \''.$this->id_report.'\' and indice2 = \''.$id_filtro.'\'');
        $query instanceof MDB2_Result_Common;
        if ($result = $query->fetchOne()) {
            return $result;
        } else {
            return $this->filters[$id_filtro]['default'];
        }
    }

    //----------------------------------------------------------------------------------

    /**
     * Realiza un fetch dentro de los filtros.
     */
    public function fetchFilter() {
        if (isset($this->filters)) {
            return each($this->filters);
        }
        return false;
    }

    //----------------------------------------------------------------------------------

    /**
     * Devuelve un arreglo con los valores de los filtros.
     */
    private function getFilterValues() {
        $result = array();

        # Obtengo el query como se encontraba en un comienzo.
        $query = $this->jsonQueryReport['query'];

        if ($filter = $this->filters) {
            foreach ($filter as $key => $value) {
                $result[$key] = $this->getFilterValue($key);

                # Agrego los comodines si es que aparecen en el query.
                $queryArray = explode('?',$query);
                if (substr($queryArray[$key],-1) == '_') {
                    $result[$key] = '_'.$result[$key];
                }
                if (substr($queryArray[$key],-1) == '%') {
                    $result[$key] = '%'.$result[$key];
                }
                if (substr($queryArray[$key+1],0,1) == '_') {
                    $result[$key] = $result[$key].'_';
                }
                if (substr($queryArray[$key+1],0,1) == '%') {
                    $result[$key] = $result[$key].'%';
                }
            }
        }
        return $result;
    }

    //----------------------------------------------------------------------------------

    /**
     * Se encarga de modificar el valor por defecto de cada uno de los filtros según
     * se haya consultado en ellos. De esta manera se podrá recuperar siempre la última
     * consulta hecha desde este campo.
     */
    private function guardarFiltrosDefault() {
        foreach ($this->filterValues as $key => $value) {
            $daoConfiguracion = Denko :: daoFactory ('Configuracion');
            $daoConfiguracion instanceof DataObjects_Configuracion;
            $daoConfiguracion->indice1 = $this->id_report;
            $daoConfiguracion->indice2 = $key;
            if ($daoConfiguracion->find(true)) {
                $daoConfiguracion->nombre = str_replace(' ','_',$this->filters[$key]['nombre']);
            	$daoConfiguracion->valor  = $value;
                $daoConfiguracion->update();
            } else {
                $daoConfiguracion->nombre      = str_replace(' ','_',$this->filters[$key]['nombre']);
            	$daoConfiguracion->estado      = 1;
            	$daoConfiguracion->valor       = $value;
                $daoConfiguracion->tipo        = $this->filters[$key]['tipo'];
                $daoConfiguracion->descripcion = 'dkr_filter';
                $daoConfiguracion->insert();
            }
        }
    }

    //----------------------------------------------------------------------------------
    //------------------------------------ PAGINADO ------------------------------------
    //----------------------------------------------------------------------------------

    /**
     * Retorna el total de páginas a mostrar.
     */
    public function getTotalPages() {
        if ($this->numRows > 0 && $this->resultsPerPage > 0) {
            return ceil($this->numRows / $this->resultsPerPage);
        }
        return 1;
    }

    //----------------------------------------------------------------------------------

    /**
     * Establece el total de páginas a mostrar, en base al número de resultados y
     * los resultados por páginas ya establecidos.
     */
    private function setNumRows() {
        $this->setDao();
        $this->numRows = $this->_dao->numRows();
    }

    //----------------------------------------------------------------------------------
    //----------------------------------------------------------------------------------
    //----------------------------------------------------------------------------------
}
