<?php
//require_once 'Validate.php';
require_once 'dk.denko.php';
require_once 'common.php';

class PHPCalc {


    private $sheet = null;
    private $width = 1;
    private $height = 1;
    public $calculate = true;
    private static $invalidCell = '#ERROR#';
    private static $recursionControl = '#RECURSION#';
    private static $noVisibleCell = '#NOVISIBLE#';
    private static $typeStatic = 0;
    private static $typeInput = 1;
    private static $typeImage = 2;
    private $no_visible_cells = array();

    /*
    *
    * @param string $metadata (JSON representando la planilla de cálculo.
    */
    public function __construct ($metadata, $height = null, $width = null) {
        $sheet = json_decode ($metadata, true);
        Denko::arrayUtf8Decode ($sheet);
        $this->sheet = $sheet;
        $this->width = ($width != null) ? $width : $this->calcWidth ();
        $this->height = ($height != null) ? $height : $this->calcHeight ();
        $this->inicSheet ();

    }

    /*
    * Setea las celdas no visibles en el arreglo no_visible_cells debido a los colspan y rowspan.
    *
    */
    public function inicSheet () {
        $no_visible = array();
        foreach ($this->sheet as $fil => $columnas) {
            foreach ($columnas as $col => $celda) {
                $colspan = !empty($celda['colspan']) ? $celda['colspan'] : 1;
                if ($colspan > 1) {
                    $colum = $col;
                    for ($i = 1; $i < $colspan; $i++) {
                        $no_visible[$fil][++$colum] = true;
                    }
                }
                if (!empty($celda['rowspan'])) {
                    for ($f = 1; $f < $celda['rowspan']; $f++) {
                        $colum = $col;
                        for ($c = 1; $c <= $colspan; $c++) {
                            $no_visible[$fil + $f][$colum++] = true;
                        }
                    }

                }
            }

        }
        $this->no_visible_cells = $no_visible;
        return;
    }


    private function calcWidth () {
        $width = 0;
        foreach ($this->sheet as $fil => $cols) {
            $cant = ord (end (array_keys ($cols))) - 64;
            if ($cant > $width) {
                $width = $cant;
            }
        }
        return $width;
    }

    private function calcHeight () {
        return end (array_keys ($this->sheet));
    }

    public function sheetToJson () {
        $sheet = $this->sheet;
        Denko::arrayUtf8Encode ($sheet);
        return json_encode ($sheet);
    }

    private function indice ($col) {
        return chr ($col + 64);
    }

    //------------------------------------------------------------------------------------------------------------------------------
    /**
     * Obtiene el valor de una celda
     * @param integer $fil
     * @param string $col
     * @throws Exception
     * (Cuando los valores de la celda no son los correctos o no existe la celda)
     * @return string|integer
     */
    public function getCellValue ($fil, $col) {
        if (!isset($this->sheet[$fil][$col])) {
            throw new Exception("Celda [$col:$fil] inexistente.");
        }
        if ($this->sheet[$fil][$col]['valor'] == self::$recursionControl) {
            throw new Exception("Recursión que incluye la celda [$col:$fil].");
        }

        if ($this->sheet[$fil][$col]['valor'] != self::$invalidCell && $this->sheet[$fil][$col]['valor'] != self::$noVisibleCell) {
            //TODO: Verificar tipo de celda y luego aplicar validación de tipo de dato usando el validate de pear;
            if (!empty($this->sheet[$fil][$col]['valor'])) return $this->sheet[$fil][$col]['valor'];

            //Si tiene fórmula asociada calculo el valor
            if (!empty($this->sheet[$fil][$col]['formula'])) {
                $this->calculateCell ($fil, $col);
            }
        }
        if ($this->sheet[$fil][$col]['valor'] == self::$invalidCell) {
            throw new Exception("Valor de celda [$col:$fil] inválido");
        }
        if ($this->sheet[$fil][$col]['valor'] == self::$noVisibleCell) {
            throw new Exception("Celda no visible [$col:$fil].");
        }
        if ($this->sheet[$fil][$col]['valor'] === '') {
            throw new Exception("Celda vacia [$col:$fil] - El usuario debe completarla");
        }
        return $this->sheet[$fil][$col]['valor'];
    }

    //------------------------------------------------------------------------------------------------------------------------------
    /**
     * Calcula el valor de una celda si la misma tiene una formula asociada.
     * @param integer $fil
     * @param string $col
     * @throws ErrorException
     * @return boolean
     */
    public function calculateCell ($fil, $col) {
        if (!empty($this->sheet[$fil][$col]['valor'])) return;
        if (empty($this->sheet[$fil][$col]['formula'])) return;
        $valor = $this->sheet[$fil][$col]['formula'];
        $pattern = '/@IF\((.+),(.+),(.+)\)/';
        $replacement = '((${1})?(${2}):(${3}))';
        $valor = preg_replace ($pattern, $replacement, $valor);
        $pattern = '/#([A-Z]+)([0-9]+)/';
        $replacement = '$this->getCellValue(${2},\'${1}\')';
        $valor = preg_replace ($pattern, $replacement, $valor);
        $pattern = '/@([A-Z0-9]+)\(([A-Z]+)([0-9]+):([A-Z]+)([0-9]+)\)/';
        $replacement = '$this->internal_${1}_range(${3},\'${2}\',${5},\'${4}\')';
        $valor = preg_replace ($pattern, $replacement, $valor);
        $pattern = '/@([A-Z0-9]+)\(/';
        $replacement = '$this->internal_${1}(';
        $valor = preg_replace ($pattern, $replacement, $valor);
        $valor = str_replace ('@', '$this->internal_', $valor);
        try {
            set_error_handler ("myErrorHandler");
            $this->sheet[$fil][$col]['valor'] = self::$recursionControl;
            try {
                eval('$result=' . $valor . ';');
            } catch (Exception $e) {
                throw new ErrorException("Celda [$col:$fil] : " . $e->getMessage ());
            }
            $this->sheet[$fil][$col]['valor'] = $result;
            restore_error_handler ();

        } catch (Exception $e) {
            $this->sheet[$fil][$col]['valor'] = self::$invalidCell;
            echo $e->getMessage () . "<br/>";
        }
    }

    //------------------------------------------------------------------------------------------------------------------------------
    /**
     * Seteo los valores ala planilla
     * @param array $celdas (Valores de celdas correspondientes a inputs)
     */
    public function setValues ($celdas) {
        foreach ($celdas as $key => $celda) {
            $indice = explode ('_', $key);
            $this->sheet[$indice[1]][$indice[0]]['valor'] = $celda;
        }
    }

    //------------------------------------------------------------------------------------------------------------------------------

    /**
     * Recorre todas las celdas calculado su valor cuando corresponda.
     */

    public function calculateSheet () {
        foreach ($this->sheet as $fil => $columna) {
            $ids_columna = array_keys ($columna);
            foreach ($ids_columna as $col) {
                if (!empty($this->sheet[$fil][$col]['formula'])) {
                    $this->sheet[$fil][$col]['valor'] = null;
                }
            }
        }
        foreach ($this->sheet as $fil => $columna) {
            $ids_columna = array_keys ($columna);
            foreach ($ids_columna as $col) {
                $this->calculateCell ($fil, $col);
            }
        }
    }

    //------------------------------------------------------------------------------------------------------------------------------
    public function drawSheetTable ($cellpadding = 0, $cellspacing = 0, $className = '',$editable=true) {
        $html = '<table class="' . $className . '" cellpadding="' . $cellpadding . '" cellspacing="' . $cellspacing . '">';
        $html .= '<tr><th></th>';
        $letter = 'A';
        for ($c = 1; $c <= $this->getWidth (); $c++) {
            $html .= '<th>' . $letter++ . '</th>';
        }
        $html .= '</tr>';
        for ($fil = 1; $fil <= $this->getHeight (); $fil++) {
            $html .= '<tr>';
            $html .= '<th>' . $fil . '</th>';
            for ($c = 1; $c <= $this->getWidth (); $c++) {
                $col = $this->indice ($c);
                if (!empty($this->no_visible_cells[$fil][$col])) {
                    continue;
                }
                $celda = !empty($this->sheet[$fil][$col]) ? $this->sheet[$fil][$col] : null;
                $colspan = !empty($celda['colspan']) ? $celda['colspan'] : 1;
                $rowspanValue = (!empty($celda['rowspan'])) ? ' rowspan = "' . $celda['rowspan'] . '" ' : '';
                $classValue = (!empty($celda['class'])) ? ' class= "' . $celda['class'] . '" ' : '';
                $styleValue = (!empty($celda['style'])) ? ' style= "' . $celda['style'] . '" ' : '';
                if ($celda != null) {
                    $html .= '<td ' . $classValue . $styleValue . ' colspan="' . $colspan . ' "' . $rowspanValue . '>';
                    switch ($celda['type']) {
                        case self::$typeInput:
                            $html .= ($editable)?'<input name="celdas[' . $col . '_' . $fil . ']" type="text" value="' . $celda['valor'] . '" />':$celda['valor'];
                            break;
                        case self::$typeImage:
                            $html .= '<img src="' . $celda['valor'] . '" alt="" />';
                            break;
                        case self::$typeStatic:
                            $html .= $celda['valor'];
                            break;
                        default:
                            $html .= $celda['valor'];
                            break;
                    }
                    //$html .= '<br/>F: ' . $celda['formula'] . '</td>';
                    $html .= '</td>';
                }
                else {
                    $html .= '<td>&nbsp;</td>';
                }
            }
            $html .= '</tr>';
        }
        $html .= '</table>';
        if ($editable){
            $html.='<input type="hidden" name="sheet" value="' . htmlspecialchars ($this->sheetToJson ()) . '" /><br/>
            <input type="submit" class="submit" value="Calcular" />';
        }
        return $html;
    }

    //--------------------------------------------------------------------------------------------------------------------------------
    public function getHeight () {
        return $this->height;
    }

    //--------------------------------------------------------------------------------------------------------------------------------
    public function getWidth () {
        return $this->width;
    }

    //--------------------------------------------------------------------------------------------------------------------------------
    public function debug () {
        return print_r ($this->sheet);
    }

    //--------------------------------------------------------------------------------------------------------------------------------
    public function internal_SQRT ($val) {
        return sqrt ($val);
    }

    //--------------------------------------------------------------------------------------------------------------------------------
    public function checkReverseRange (&$a, &$b) {
        if (strlen ($a) < strlen ($b) || (strlen ($a) == strlen ($b) && $a <= $b)) return;
        $c = $a;
        $a = $b;
        $b = $c;
    }

    //--------------------------------------------------------------------------------------------------------------------------------
    public function internal_SUM_range ($f1, $c1, $f2, $c2) {
        $this->checkReverseRange ($f1, $f2);
        $this->checkReverseRange ($c1, $c2);
        $res = 0;
        for ($f = $f1; $f <= $f2; $f++) {
            for ($c = $c1; strlen ($c) < strlen ($c2) || (strlen ($c) == strlen ($c2) && $c <= $c2); $c++) {
                $res += $this->getCellValue ($f, $c);
            }
        }
        return $res;
    }
    //--------------------------------------------------------------------------------------------------------------------------------
}

//-------------------------------------------------------------------------------------------------------------------------------------
//-------------------------------------------------------------------------------------------------------------------------------------
