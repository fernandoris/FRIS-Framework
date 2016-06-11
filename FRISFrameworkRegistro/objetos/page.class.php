<?php

/**
 * Este es el objeto page
 * Es un objeto separadao para permitir alguna funcionalidad extra intersante
 * Por ejemplo: paginas con password, a�adir css y js especificos, etc
 */
class Page {

    // room to grow later?
    private $css = array();
    private $js = array();
    private $bodyTag = '';
    private $bodyTagInsert = '';

    // futura funcionalidad?
    private $authorised = true;
    private $password = '';

    // elementos de la pagina
    private $title = '';
    private $tags = array();
    private $postParseTags = array();
    private $bits = array();
    private $content = "";

    /**
     * Constructor...
     */
    function __construct() {
    }

    public function getTitle() {
        return $this -> title;
    }

    public function setPassword($password) {
        $this -> password = $password;
    }

    public function setTitle($title) {
        $this -> title = $title;
    }

    public function setContent($content) {
        $this -> content = $content;
    }

    public function addTag($key, $data) {
        $primerTag = array($key => $data);	
        $this -> tags = $primerTag + $this->tags;
    }

    public function getTags() {
        return $this -> tags;
    }

    public function addPPTag($key, $data) {
        $this -> postParseTags[$key] = $data;
    }

    public function addCss($bitcss) {
        $this -> css[] = STARTPATH . "skins/" . FRIS::getSetting('skin') . "/css/".$bitcss;
    }
	
	public function addImg($img) {
		$this -> tags[$img] = STARTPATH . "skins/" . FRIS::getSetting('skin') . "/images/".$img;
	}

    public function addJs($bitjs) {
    	if(file_exists(APP_PATH. "/skins/" . FRIS::getSetting('skin') . "/js/".$bitjs)){
    		$this -> js[] = STARTPATH . "skins/" . FRIS::getSetting('skin') . "/js/".$bitjs;
    	}else{
    		$this -> js[] = $bitjs;
    	}        
    }

    /**
     * Devuelve los tags para ser parseados despu�s de la primera tanda
     * @return array
     */
    public function getPPTags() {
        return $this -> postParseTags;
    }

    /**
     * A�ade un template bit a la p�gina,no se va a a�adir el contenido ahora
     * @param String tag donde el template es a�adido
     * @param String nombre de archivo del template
     * @return void
     */
    public function addTemplateBit($tag, $bit) {
        $this -> bits[$tag] = $bit;
    }

    /**
     * Devuelve los template bits para introducirlos en la pagina
     * @return array de tags del template y los nombres de archivo de los templates
     */
    public function getBits() {
        return $this -> bits;
    }

    /**
     * Devuelve un poco del contenido de la pagina
     * @param String los tags que delimitan el bloque ( <!-- START tag --> block <!-- END tag --> )
     * @return String the bloque de contenido
     */
    public function getBlock($tag) {
        preg_match('#<!-- START ' . $tag . ' -->(.+?)<!-- END ' . $tag . ' -->#si', $this -> content, $tor);

        $tor = str_replace('<!-- START ' . $tag . ' -->', "", $tor[0]);
        $tor = str_replace('<!-- END ' . $tag . ' -->', "", $tor);

        return $tor;
    }

    public function getContent() {
        return $this -> content;
    }

    public function getCss() {
        return $this -> css;
    }

    public function getJs() {
        return $this -> js;
    }

    public function setSSL($swhitch) {
        if ($swhitch == true) {
            if (!isset($_SERVER['HTTPS']) || strtolower($_SERVER['HTTPS']) != 'on') {
                header("Location: " . "https://" . $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"]);
            }
        } else {
             if (isset($_SERVER['HTTPS'])) {
                header("Location: " . "http://" . $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"]);
            }
        }
    }
	
	public function printTags(){
		print_r($this->tags);
	}

}
?>