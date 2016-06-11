<?php

// Preveemos que el objeto no se llame desde fuera del framework
if (!defined('FRISFW')) {
    echo 'Este archivo solo puede ser llamadoThis file can only be called via the main index.php file, and not directly';
    exit();
}

/**
 * Clase del gestor de templates
 */
class template {

    private $page;
    private $protocoloElegido = false;

    public function __construct() {
        include (APP_PATH . '/FRISFrameworkRegistro/objetos/page.class.php');
        $this -> page = new Page();
        //  echo APP_PATH . "FRISFrameworkRegistro/objetos/page.class.php\n";
    }

    /**
     * A�ade un bit template a nuestra p�gina
     * @param String $tag la etiqueta donde insertaremos el template ej. {hello}
     * @param String $bit el template bit (ruta al archivo, o el nombre del fichero)
     * @return void
     */
    public function addTemplateBit($tag, $bit) {
        if (strpos($bit, 'skins/') === false) {
            $bit = APP_PATH . '/skins/' . FRIS::getSetting('skin') . '/templates/' . $bit;
        }
        $this -> page -> addTemplateBit($tag, $bit);
    }

    /**
     * Coloca los template bits en nuestra pagina de contenido
     * y actualiza el contenido de la p�gina
     * @return void
     */
    private function replaceBits() {
        $bits = $this -> page -> getBits();
        foreach ($bits as $tag => $template) {
            if (file_exists($template) == true) {
                $templateContent = file_get_contents($template);
                $newContent = str_replace('{@' . $tag . '}', $templateContent, $this -> page -> getContent());
                $this -> page -> setContent($newContent);
            }else{
                throw new Exception("Error de template: no se encuentra el archivo $template ", 1);
            }
        }
    }

    /**
     * Reemplaza los tags de nuestra pagina por contenido
     * @return void
     */
    private function replaceTags() {
        // devuelve el tag
        $tags = $this -> page -> getTags();
        // desglosa el contenido del tag
        foreach ($tags as $tag => $data) {
            if (is_array($data)) {

                if ($data[0] == 'SQL') {
                    // esto es un query en cache ... reemplaza los DB tags
                    $this -> replaceDBTags($tag, $data[1]);
                } elseif ($data[0] == 'DATA') {
                    // esto son datos en cache...reemplaza data tags
                    $this -> replaceDataTags($tag, $data[1]);
                }
            } else {
                // reemplaza contenido
                $newContent = str_replace('{' . $tag . '}', $data, $this -> page -> getContent());
                // actualiza el contenido de la pagina
                $this -> page -> setContent($newContent);
            }
        }
    }

    /**
     * Reemplaza el contendio en la pagina por los datos de la DB
     * @param String $tag el tag que identifica el area de contenido
     * @param int $cacheId los id de queries en cache
     * @return void
     */
    private function replaceDBTags($tag, $cacheId) {
        $block = '';
        $blockOld = $this -> page -> getBlock($tag);

        // para cada registro que devuelve el query...
        while ($tags = FRIS::getObject('db') -> resultsFromCache($cacheId)) {
            $blockNew = $blockOld;
            // crea un nuevo bloque de contenido con los resultados reemplazados en el
            foreach ($tags as $ntag => $data) {
                $blockNew = str_replace('{' . $ntag . '}', $data, $blockNew);
            }
            $block .= $blockNew;
        }
        $pageContent = $this -> page -> getContent();
        // quita los separadores en el template limpiando html
        $newContent = str_replace('<!-- START ' . $tag . ' -->' . $blockOld . '<!-- END ' . $tag . ' -->', $block, $pageContent);
        // actualiza el contenido de la pagina
        $this -> page -> setContent($newContent);
    }

    /**
     * Reemplaza el contenido en la p�gina por los datos en cache
     * @param String $tag el tag que define el area de contenido
     * @param int $cacheId el id de los datos en la cache
     * @return void
     */
    private function replaceDataTags($tag, $cacheId) {
        $block = $this -> page -> getBlock($tag);
        $blockOld = $block;
        while ($tags = FRIS::getObject('db') -> dataFromCache($cacheId)) {
            foreach ($tags as $tag => $data) {
                $blockNew = $blockOld;
                $blockNew = str_replace('{' . $tag . '}', $data, $blockNew);
            }
            $block .= $blockNew;
        }
        $pageContent = $this -> page -> getContent();
        $newContent = str_replace($blockOld, $block, $pageContent);
        $this -> page -> setContent($newContent);
    }

    /**
     * Devuelve el objeto pagina
     * @return Object
     */
    public function getPage() {
        return $this -> page;
    }

    /**
     * Cambia el contenido de la pagina dependiendo del numero de templates
     * pasa las rutas de los templates como argumentos.
     * @param String $bit el bit del template
     * @return void
     */
    public function buildFromTemplates($bit) {
        $content = $this -> exploreTemplates($bit);
        $this -> page -> setContent($content);
    }

    /**
     * Explora el template de forma recursiva systituyendo sus bits por el contenido de cada uno
     * @param array $bits El array de bits del template
     * @param String $content el contenido del template a analizar
     * @return String $conten el contenido una vez tratado
     */
    public function exploreTemplates($bits, $content = "") {
        if (strlen($content) == 0)
            $bits = array($bits);
        $bits2 = array();
        foreach ($bits as $bit) {
            $tag = $bit;
            if (strpos($bit, 'skins/') === false) {
                $bit = 'skins/' . FRIS::getSetting('skin') . '/templates/' . $tag . ".html";
                $bitcss = 'skins/' . FRIS::getSetting('skin') . '/css/' . $tag . ".css";
                $bitjs = 'skins/' . FRIS::getSetting('skin') . '/js/' . $tag . ".js";
            }

            if (file_exists($bitcss) == true) {
                $bitcss = str_replace('skins/' . FRIS::getSetting('skin') . '/css/', '', $bitcss);
                $this -> page -> addCss($bitcss);
            }
			
            if (file_exists($bitjs) == true) {
                $bitjs = str_replace('skins/' . FRIS::getSetting('skin') . '/js/', '', $bitjs);
                $this -> page -> addJs($bitjs);
            }
            if (file_exists($bit) == true) {
                $subcontent = file_get_contents($bit);
                if (strlen($content) > 0) {
                    $content = str_replace('{@' . $tag . '}', $subcontent, $content);
                } else {
                    $content = $subcontent;
                }
                $bits2 = array_merge($bits2, $this -> getBitsFromTemplate($subcontent));
            }
        }
        if (count($bits2) > 0) {
            $content = $this -> exploreTemplates($bits2, $content);
        }
        return $content;
    }

    /**
     * Analiza el contenido del template y devuelve un array con sus bits de template
     * @param String conetnido del template
     * @return array el array de bits
     */
    public function getBitsFromTemplate($content) {
        $bits = array();
        $start = false;
        $bit = "";
        for ($i = 0; $i < strlen($content); $i++) {
            if ($start) {
                $bit .= $content[$i];
                if ($content[$i + 1] == '}') {
                    $start = false;
                    $bits[] = $bit;
                    $bit = "";
                    $i = $i + 1;
                }
            }
            if ($content[$i] == '{' && $content[$i + 1] == '@') {
                $start = true;
                $i = $i + 1;
            }
        }
        return $bits;
    }

    /**
     * Convierte una tabla de datos (ej. un registro de la db) en algunos tags
     * @param array los datos
     * @param string un prefijo a�adido al nombre del campo para crear el tagname
     * @return void
     */
    public function dataToTags($data, $prefix) {
        foreach ($data as $key => $content) {
            $this -> page -> addTag($key . $prefix, $content);
        }
    }

    public function parseTitle() {
        $newContent = str_replace('<title>', '<title>' . $this -> page -> getTitle(), $this -> page -> getContent());
        $this -> page -> setContent($newContent);
    }

    public function parseCss() {
        $bits = $this -> page -> getCss();
        foreach ($bits as $key => $value) {
            $newContent = str_replace('<head>', '<head>' . ' 
					<link rel="stylesheet" href="' . $value . '" media="screen"> ', $this -> page -> getContent());
            $this -> page -> setContent($newContent);
        }
    }

    public function parseJs() {
        $bits = $this -> page -> getJs();
        $bits = array_reverse($bits);
        foreach ($bits as $key => $value) {
            $newContent = str_replace('<head>', '<head>' . ' 
					<script src="' . $value . '" type="text/javascript" charset="'.DBCHARSET.'"></script>', $this -> page -> getContent());
            $this -> page -> setContent($newContent);
        }
    }
    
    public function setSSL($swhitch){
        $this -> page -> setSSL($swhitch);
        $this -> protocoloElegido = true;
    }

    /**
     * Parsea el objeto registro en la salida
     * @return void
     */
    public function parseOutput() {
        if($this -> protocoloElegido == false) $this -> setSSL(false);
        $this -> replaceBits();
        $this -> replaceTags();
        $this -> parseTitle();
        $this -> parseCss();
        $this -> parseJs();
    }

}
?>
