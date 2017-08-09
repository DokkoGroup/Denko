<?php
require_once '../denko/dk.denko.php';

/**
 * Abro la session
 */
Denko::sessionStart();

/**
 *
 */
class DK_Captcha{

    public $width;
    public $height;
    public $img;
    public $cualRandom;   // decide la tonalidad del color del texto.
    public $claroOscuro;  // Decide si el texto es claro u oscuro.
    public $margenColor;  // Margen para el color de la fuente.
    public $margenMedio;  // Margen para los colores de fondo.
    public $ruidoQueTapa; // Si es cero no tapa nada, si es uno 50% si es 2 33%, si es 3 25% y asi sucesivamente.
    public $cantLineasQueTapan;
    public $background_color;
    public $textComplet;
    public $max_letter;

    /**
     * Constructora
     *
     * @access public
     */
    public function __construct($width, $height, $margColor=150, $margMedio=180, $ruido=0, $cantLineas=4, $max_letter=6){



        $this->width  = $width;
        $this->height = $height;
        $this->img    = @imagecreate($width, $height) or die("Cannot Initialize new GD image stream");

        $this->cualRandom  = rand(0,2);
        $this->claroOscuro = rand(0,3);

        $this->margenColor  = $margColor;
        $this->margenMedio  = $margMedio;
        $this->ruidoQueTapa = $ruido;
        $this->cantLineasQueTapan = $cantLineas;
        $this->textComplet = '';
        $this->max_letter = $max_letter;

    }

    /**
     * Setea el fondo de la imagen
     *
     * @access public
     * @return void
     */
    public function setBackgroundColor(){
        $claroOscuro = $this->claroOscuro;
        $cualRandom = $this->cualRandom;
        $im = $this->img;
        $margenColor = $this->margenColor;
        $margenMedio = $this->margenMedio;

        if($claroOscuro==0){
            switch($cualRandom){
                case 0: $bg_color = imagecolorallocate($im, rand(255-$margenColor,255), 205, 0); break;
                case 1: $bg_color = imagecolorallocate($im, 205, rand(255-$margenColor,255), 0); break;
                case 2: $bg_color = imagecolorallocate($im, 0, 255, rand(255-$margenColor,255)); break;
            }
            $rangoR1=rand(0,127-$margenMedio/2);
            $rangoR2=rand(127-$margenMedio/2,255-$margenMedio);
            $rangoG1=rand(0,127-$margenMedio/2);
            $rangoG2=rand(127-$margenMedio/2,255-$margenMedio);
            $rangoB1=rand(0,127-$margenMedio/2);
            $rangoB2=rand(127-$margenMedio/2,255-$margenMedio);
        }

        else{
            switch($cualRandom){
                case 0: $bg_color = imagecolorallocate($im, rand(0,$margenColor), 50, 0); break;
                case 1: $bg_color = imagecolorallocate($im, 60, rand(0,$margenColor), 0); break;
                case 2: $bg_color = imagecolorallocate($im, 0, 60, rand(0,$margenColor)); break;
            }
            $rangoR1=rand($margenMedio,127+$margenMedio/2);
            $rangoR2=rand(127+$margenMedio/2,255);
            $rangoG1=rand($margenMedio,127+$margenMedio/2);
            $rangoG2=rand(127+$margenMedio/2,255);
            $rangoB1=rand($margenMedio,127+$margenMedio/2);
            $rangoB2=rand(127+$margenMedio/2,255);
        }

        $this->background_color = $bg_color;
        for($i=0; $i<250; $i++){
            imagecolorallocate($im, rand($rangoR1,$rangoR2), rand($rangoG1,$rangoG2), rand($rangoB1,$rangoB2));
        }
    }

    /**
     *
     */
    public function setPixelImg(){
        for($x=0; $x<$this->width; $x++){
            for($y=0; $y<$this->height; $y++){
                imagesetpixel($this->img, $x, $y, rand(0,255));
            }
        }
    }

    /**
     * Pone el texto completo sobre la imagen.
     */
    protected function putCharacter($char,$nroLetter,$widLetter){
        $angulo=rand(-15,15);
        $letter='../captcha/fonts/'.rand(1,7).'.ttf';
        imagettftext($this->img, 28, $angulo, 5+$widLetter*($nroLetter++), 55+$angulo, $this->background_color, $letter, $char);
    }

    /**
     * Retorna el texto de la imagen
     */
    public function getCompletedText(){
        $textComplet = '';
        for($nroLetter=0; $nroLetter<$this->max_letter; $nroLetter++){
            if(rand(0,2)==1){
                $char = chr(rand(65,90));
            }else{
                $char = rand(0,9);
            }
            $this->putCharacter($char,$nroLetter,29);
            $textComplet.=$char;
        }

        $this->textComplet = $textComplet;
    }

    /**
     * Imprime lineas sobre la imagen.
     *
     * @access public
     * @return void
     */
    public function putLine(){
        $im = $this->img;
        $width = $this->width;
        $height = $this->height;
        $bg_color = $this->background_color;

        for($i=0; $i < $this->cantLineasQueTapan; $i++){
            imageline($im, rand(0,$width), rand(0,$height), rand(0,$width), rand(0,$height), $bg_color);
        }
    }

    /**
     * Aplica un distorsion (ruido) a la imagen.
     *
     * @access public
     * @return void
     */
    public function setNoise(){
        for($x=0; $x<$this->width; $x++){
            for($y=0; $y<$this->height; $y++){
                if(rand(0,$this->ruidoQueTapa)==1){
                    imagesetpixel($this->img, $x, $y, rand(0,255));
                }
            }
        }
    }

    /**
     *
     */
    public function make(){
        $this->setBackgroundColor();
        $this->setPixelImg();
        $this->putLine();
        $this->setNoise();
        $this->getCompletedText();
        imagepng($this->img);
    }

    /**
     *
     */
    function draw(){
        header("Cache-control: no-cache");
        header("Content-type: image/png");
        Denko::sessionStart();
        $_SESSION['codigo_secreto'] = $this->textComplet;
        $_SESSION['codigo_ok']=false;
    }

    /**
     * Destruye la imagen
     *
     * @access public
     * @return void
     */
    public function destroy(){
        imagedestroy($this->img);
    }

    /**
     * Reduce la dificultad de comparacion con el texto del captcha
     */
    public static function reduceStrictness($text){
        $text=strtoupper($text);
        $text=str_replace('O','0',$text);
        $text=str_replace('I','1',$text);
        $text=str_replace('L','1',$text);
        $text=str_replace('G','6',$text);
        $text=str_replace('S','5',$text);
        return $text;
    }

    /**
     * Verifica si el texto ingresado es igual al texto del captcha.
     * @param $text texto a comparar
     * @param bool $easy indica si se revisa con reduceStrictness.
     * @return bool indica si el texto coincide
     */
    public static function verify($text, $easy=true) {
        if (empty($_SESSION['codigo_secreto'])) return false;
        if($easy) return self::reduceStrictness($_SESSION['codigo_secreto']) == self::reduceStrictness($text);
        return $_SESSION['codigo_secreto']==$text;
    }

}
