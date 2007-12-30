<?php
/**
* @package script
* @author  Yannick Le Guédart (yannick at over-blog dot com)
*/

class defaultCtrl extends jControllerCmdLine 
{
    protected $allowed_options = 
        array 
        (
            'changeActionSelector' => 
                array 
                (
                    '--directory' => true
                )
        );

    protected $allowed_parameters =
        array 
        (
            'changeActionSelector' => array ()
        );

    private $_rep;
    private $_directory;
    private $_destDirectory;

    /**
    * changeActionSelector
    *
    * The paramaters : 
    * 
    *    --directory : the base directory in the Jelix app (modules by default)
    *
    * @access public
    * @author Yannick Le Guédart (yannick at over-blog dot com)
    * @version 1.0
    */

     public function changeActionSelector ()
    {
        $this->_rep = $this->getResponse('text');

        // ---------------------------------------------------------------------
        // Récupération des options
        // ---------------------------------------------------------------------

        $this->_directory = $this->option ('--directory');

        if (empty ($this->_directory))
        {
            $this->_directory = JELIX_APP_PATH .'modules';
        }

        $this->_rep->content .= "Directory : $this->_directory\n";

        // ---------------------------------------------------------------------
        // nettoyage du répertoire temporaire de destination
        // ---------------------------------------------------------------------

        $this->_destDirectory = JELIX_APP_TEMP_PATH . 'changeActionSelector';

        `rm -Rf  $this->_destDirectory`;

        $this->_rep->content .= 
            "Creating destination directory : [$this->_destDirectory]\n";

        mkdir ($this->_destDirectory);

        // ---------------------------------------------------------------------
        // Modifications
        // ---------------------------------------------------------------------

        $this->_jUrlGet ();
        $this->_jUrlTemplate ();
        $this->_redirectAction ();

        return $this->_rep;
    }

    private function _jUrlGet ()
    {
        // ---------------------------------------------------------------------
        // Liste des fichiers
        // ---------------------------------------------------------------------

        $fileList = array ();

        $grepCmd = 
            "grep -R 'jUrl::get' " . $this->_directory . " | grep -v svn";

        foreach (split ("\n", `$grepCmd`) as $line)
        {
            $file = 
                substr 
                (
                    $line, 
                    0,
                    strpos ($line, ':')
                );

            if (!empty ($file))
            {
                if (isset ($fileList[$file]))
                {
                    $fileList[$file] ++;
                }
                else
                {
                    $fileList[$file] = 1;
                }
            }
        }

        // ---------------------------------------------------------------------
        // Traitement des fichiers
        // ---------------------------------------------------------------------

        foreach ($fileList as $file => $nb)
        {
            $this->_rep->content .= "$file [$nb] -> ";

            $content = file_get_contents ($file);
            $count = 0;

            $destPath = $this->_generatePath ($file);

            if (
                preg_match_all (
                    '/((jUrl::get\s*\(\s*)(\'|")([\w~_]+)\\3)/',
                    $content, 
                    $match,
                    PREG_SET_ORDER))
            {
                foreach ($match as $m)
                {
                    $originalJUrl     = $m[1];
                    $newJUrl        = 
                        $m[2] . $m[3] . str_replace ('_', ':', $m[4]) . $m[3];

                    $content = 
                        preg_replace (
                            '/((jUrl::get\s*\(\s*)(\'|")([\w~_]+)\\3)/',
                            $m[2] . $m[3] . str_replace ('_', ':', $m[4]) . $m[3], 
                            $content, 
                            1);

                    $count ++;
                }
            }

            $this->_rep->content .= "[$count]";

            if ($count != $nb)
            {
                $this->_rep->content .= "... NOK\n";
            }
            else
            {
                $this->_rep->content .= "... OK\n";
            }

            file_put_contents (
                $destPath,
                $content);
        }
    }

    private function _jUrlTemplate ()
    {
        // ---------------------------------------------------------------------
        // Liste des fichiers
        // ---------------------------------------------------------------------

        $fileList = array ();

        $grepCmd =  
            "grep -R '{jurl' " . $this->_directory . " | grep -v svn";

        foreach (split ("\n", `$grepCmd`) as $line)
        {
            $file = 
                substr 
                (
                    $line, 
                    0,
                    strpos ($line, ':')
                );

            if (!empty ($file))
            {
                if (isset ($fileList[$file]))
                {
                    $fileList[$file] ++;
                }
                else
                {
                    $fileList[$file] = 1;
                }
            }
        }

        // ---------------------------------------------------------------------
        // Traitement des fichiers
        // ---------------------------------------------------------------------

        foreach ($fileList as $file => $nb)
        {
            $this->_rep->content .= "$file [$nb] -> ";

            $content = file_get_contents ($file);
            $count = 0;

            $destPath = $this->_generatePath ($file);

            if (
                preg_match_all (
                    '/(({\s*jurl\s*)(\'|")([\w~_]+)\\3)/',
                    $content, 
                    $match,
                    PREG_SET_ORDER))
            {
                foreach ($match as $m)
                {
                    $originalJUrl     = $m[1];
                    $newJUrl        = 
                        $m[2] . $m[3] . str_replace ('_', ':', $m[4]) . $m[3];

                    $content = 
                        preg_replace (
                            '/(({\s*jurl\s*)(\'|")([\w~_]+)\\3)/',
                            $m[2] . $m[3] . str_replace ('_', ':', $m[4]) . $m[3], 
                            $content, 
                            1);

                    $count ++;
                }
            }

            $this->_rep->content .= "[$count]";

            if ($count != $nb)
            {
                $this->_rep->content .= "... NOK\n";
            }
            else
            {
                $this->_rep->content .= "... OK\n";
            }

            file_put_contents (
                $destPath,
                $content);
        }
    }

    private function _redirectAction ()
    {
        // ---------------------------------------------------------------------
        // Liste des fichiers
        // ---------------------------------------------------------------------

        $fileList = array ();

        $grepCmd = 
            "grep -R '\->action' " . $this->_directory . " | grep -v svn";

        foreach (split ("\n", `$grepCmd`) as $line)
        {
            $file = 
                substr 
                (
                    $line, 
                    0,
                    strpos ($line, ':')
                );

            if (!empty ($file))
            {
                if (isset ($fileList[$file]))
                {
                    $fileList[$file] ++;
                }
                else
                {
                    $fileList[$file] = 1;
                }
            }
        }

        // ---------------------------------------------------------------------
        // Traitement des fichiers
        // ---------------------------------------------------------------------

        foreach ($fileList as $file => $nb)
        {
            $this->_rep->content .= "$file [$nb] -> ";

            $content = file_get_contents ($file);
            $count = 0;

            $destPath = $this->_generatePath ($file);

            if (
                preg_match_all (
                    '/((\->action\s*=\s*)(\'|")([\w~_]+)\\3)/',
                    $content, 
                    $match,
                    PREG_SET_ORDER))
            {
                foreach ($match as $m)
                {
                    $originalJUrl     = $m[1];
                    $newJUrl        = 
                        $m[2] . $m[3] . str_replace ('_', ':', $m[4]) . $m[3];

                    $content = 
                        preg_replace (
                            '/((\->action\s*=\s*)(\'|")([\w~_]+)\\3)/',
                            $m[2] . $m[3] . str_replace ('_', ':', $m[4]) . $m[3], 
                            $content, 
                            1);

                    $count ++;
                }
            }

            $this->_rep->content .= "[$count]";

            if ($count != $nb)
            {
                $this->_rep->content .= "... NOK\n";
            }
            else
            {
                $this->_rep->content .= "... OK\n";
            }

            file_put_contents (
                $destPath,
                $content);
        }
    }

    private function _generatePath ($filePath)
    {
        $newPath = 
            str_replace 
            (
                $this->_directory . '/',
                '',
                $filePath
            );

        $basePath = $this->_destDirectory;

        $directories = split ('/', $newPath);

        $fileName = array_pop ($directories);

        foreach ($directories as $dir)
        {
            if (! file_exists ($basePath . '/' . $dir))
            {
                mkdir ($basePath . '/' . $dir);
            }

            $basePath = $basePath . '/' . $dir;
        }

        return
            str_replace
            (
                $this->_directory . '/',
                $this->_destDirectory . '/',
                $filePath
            );
    }
}

?>