<?PHP

	$folder_id_array = array();
	$folder_array = array();
	$file_array = array();
	$delete_file_array = array();
	$delete_folder_array = array();
	$zipfile = "";

	include "archive.php";
	include "scorm_library.php";
	include "scorm2004_library.php";
	include "../xmlInspector.php";
	include "../screen_size_library.php";
	include "../user_library.php";
	include "../url_library.php";

	/*
	 * Set up the paths
	 */
	$dir_path = $xerte_toolkits_site->users_file_area_full . $row['template_id'] . "-" . $row['username'] . "-" . $row['template_name'] . "/";
	$parent_template_path = $xerte_toolkits_site->basic_template_path . $row['template_framework'] . "/parent_templates/" . $row['template_name'] . "/";
	$scorm_path = $xerte_toolkits_site->basic_template_path . $row['template_framework'] . "/scorm1.2/";
    $scorm_language_relpath = $xerte_toolkits_site->module_path . $row['template_framework'] . "/scorm1.2/";
	$scorm2004_path = $xerte_toolkits_site->basic_template_path . $row['template_framework'] . "/scorm2004.3rd/";
    $scorm2004_language_relpath = $xerte_toolkits_site->module_path . $row['template_framework'] . "/scorm2004.3rd/";
    $js_path = $xerte_toolkits_site->basic_template_path . $row['template_framework'] . "/js/";

    if(isset($_REQUEST['html5']))
    {
        $export_html5 = ($_REQUEST['html5'] == 'true' ? true : false);
    }
    if(isset($_REQUEST['flash']))
    {
        $export_flash = ($_REQUEST['flash'] == 'true' ? true : false);
    }
    if (!$export_html5 && !$export_flash)
    {
        $export_html5=true;
    }

	/*
	 * Make the zip
	 */
	$zipfile = new zip_file("example_zipper_new" . time() . ".zip");
	$zipfile->set_options(array('basedir' => $dir_path, 'prepand' => "", 'inmemory' => 1, 'recurse' => 1, 'storepaths' => 1));

	/*
	 * Copy the core files over from the parent folder
	 */
	copy($dir_path . "data.xml",$dir_path . "template.xml");
	$xml = new XerteXMLInspector();
	$xml->loadTemplateXML($dir_path . 'template.xml');
	if ($fullArchive){
	  _debug("Full archive");
	  export_folder_loop($parent_template_path);
	}
	else /* Only copy used models and the common folder */
	{
      _debug("Deployment archive");
      if ($export_flash)
      {
          _debug("  use flash");
          $models = $xml->getUsedModels();
          foreach($models as $model)
          {
            _debug("copy model " . $parent_template_path . "models/" . $model . ".rlm");
            array_push($file_array, array($parent_template_path . "models/" . $model . ".rlm", ""));
          }
          /* Always add menu.rlm */
          _debug("copy model " . $parent_template_path . "models/menu.rlm");
          array_push($file_array, array($parent_template_path . "models/menu.rlm", ""));

          export_folder_loop($parent_template_path . "common/");
      }
      if ($export_html5)
      {
          _debug("  use html5");
          $models = $xml->getUsedModels();
          foreach($models as $model)
          {
              _debug("copy model " . $parent_template_path . "models_html5/" . $model . ".html");
              array_push($file_array, array($parent_template_path . "models_html5/" . $model . ".html", ""));
          }
          /* Always add menu.rlm */
          _debug("copy model " . $parent_template_path . "models_html5/menu.html");
          array_push($file_array, array($parent_template_path . "models_html5/menu.html", ""));

          export_folder_loop($parent_template_path . "common_html5/");

      }
      array_push($file_array, array($parent_template_path . $row['template_name'] . ".rlt", ""));
	}
	if(isset($_GET['local'])){
		if($_GET['local']=="true"){
			$string = file_get_contents($dir_path . "/template.xml");
			$string = str_replace($xerte_toolkits_site->site_url . $xerte_toolkits_site->users_file_area_short . $row['template_id'] . "-" . $row['username'] . "-" . $row['template_name'] . "/", "", $string);
			$fh = fopen($dir_path . "/template.xml", 'w+');
			fwrite($fh,$string);
			fclose($fh);
		}
	}
	copy_parent_files();

		/*
		 * Language support
		 */
	
	export_folder_loop($xerte_toolkits_site->root_file_path . 'languages/', false, '.xml');
	copy_extra_files();

    if ($export_flash)
    {
        /*
         * Javascript js folder
         */
        export_folder_loop($js_path, false, '.js', 'js/');
        copy_extra_files();

        /*
         * Copy engine and support files
         *
         *  From root
         */
        copy($xerte_toolkits_site->root_file_path . "XMLEngine.swf", $dir_path . "XMLEngine.swf");
        array_push($delete_file_array,  $dir_path . "XMLEngine.swf");
        copy($xerte_toolkits_site->root_file_path . "MainPreloader.swf", $dir_path . "MainPreloader.swf");
        array_push($delete_file_array,  $dir_path . "MainPreloader.swf");
        copy($xerte_toolkits_site->root_file_path . "rloObject.js", $dir_path . "rloObject.js");
        array_push($delete_file_array,  $dir_path . "rloObject.js");
        copy($xerte_toolkits_site->root_file_path . "resources.swf", $dir_path . "resources.swf");
        array_push($delete_file_array,  $dir_path . "resources.swf");
    }
	/*
	 * If scorm copy the scorm files as well
	 */
	$scorm=mysql_real_escape_string($_GET['scorm']);
	if($scorm=="true")
    {
		export_folder_loop($scorm_path, false, null, "/");
		copy_extra_files();
        if ($xml->getLanguage() != 'en-GB')
        {
            export_folder_loop($xerte_toolkits_site->root_file_path . 'languages/' . $xml->getLanguage() . '/' . $scorm_language_relpath, false, null, "/languages/js/" . $xml->getLanguage() . "/");
            copy_extra_files();
        }
        export_folder_loop($xerte_toolkits_site->root_file_path . 'languages/en-GB/' . $scorm_language_relpath, false, null, "/languages/js/en-GB/");
        copy_extra_files();
	}
    else if ($scorm=="2004")
    {
		export_folder_loop($scorm2004_path, false, null, "/");
		copy_extra_files();
        if ($xml->getLanguage() != 'en-GB')
        {
            if (is_dir($xerte_toolkits_site->root_file_path . 'languages/' . $xml->getLanguage() . '/' . $scorm2004_language_relpath))
            {
                export_folder_loop($xerte_toolkits_site->root_file_path . 'languages/' . $xml->getLanguage() . '/' . $scorm2004_language_relpath, false, null, "/languages/js/" . $xml->getLanguage() . "/");
                copy_extra_files();
            }
        }
        export_folder_loop($xerte_toolkits_site->root_file_path . 'languages/en-GB/' . $scorm2004_language_relpath, false, null, "/languages/js/en-GB/");
        copy_extra_files();
    }

	//if($scorm=="true" || $scorm=="2004"){
		//copy($dir_path . $row['template_name'] . ".rlt", $dir_path . "learningobject.rlo");
		//unlink($dir_path . $row['template_name'] . ".rlt");
		//array_push($delete_file_array,  $dir_path . "learningobject.rlo");
	//}else{
		//copy($dir_path . $row['template_name'] . ".rlt", $dir_path . "learningobject.rlt");
		//unlink($dir_path . $row['template_name'] . ".rlt");
		//array_push($delete_file_array,  $dir_path . "learningobject.rlt");
	//}
	$rlo_file = $row['template_name'] . ".rlt";
	/*
	 * if used copy extra folders
	 */
	/*
	 *  jmol
	 */
	if ($xml->modelUsed("jmol"))
	{
		export_folder_loop($xerte_toolkits_site->root_file_path . "JMolViewer/");
		copy_extra_files();
	}
	/*
	 * mapstraction
	 */
	if ($xml->modelUsed("mapstraction"))
	{
		export_folder_loop($xerte_toolkits_site->root_file_path . "mapstraction/");
		copy_extra_files();
	}
	/*
	 * mediaViewer
	 */
	if ($xml->mediaIsUsed())
	{
	   export_folder_loop($xerte_toolkits_site->root_file_path . "mediaViewer/");
	   copy_extra_files();
	}

	export_folder_loop($dir_path);

    /*
     * Get the name of the learning object
     */
    $lo_name = $xml->getName();

	/*
	 * Create scorm manifests or a basic HTML page
	 */
	if($scorm=="true"){
        $useflash = ($export_flash && !$export_html5);
        if(isset($_GET['data'])){
            if($_GET['data']==true){

                    $query = "select * from " . $xerte_toolkits_site->database_table_prefix ."templatesyndication where template_id = " . mysql_real_escape_string($_GET['template_id']);
                    $query_response_metadata = mysql_query($query);
                    $metadata = mysql_fetch_array($query_response_metadata);
                    $query = "select * from " . $xerte_toolkits_site->database_table_prefix ."templaterights, " . $xerte_toolkits_site->database_table_prefix ."logindetails  where template_id = " . mysql_real_escape_string($_GET['template_id']) . " and login_id = user_id";
                    $query_response_users = mysql_query($query);
                    lmsmanifest_create_rich($row, $metadata, $query_response_users, $useflash, $lo_name);
                }
        }
        else
        {
            lmsmanifest_create($row['zipname'], $useflash, $lo_name);
		}
        if ($useflash)
        {
		    scorm_html_page_create($row['template_name'],$row['template_framework'], $rlo_file, $lo_name, $xml->getLanguage());
        }
        else
        {
            scorm_html5_page_create($row['template_framework'], $lo_name, $xml->getLanguage());
        }
	}else if ($scorm=="2004"){
        $useflash = ($export_flash && !$export_html5);
		lmsmanifest_2004_create($row['zipname'], $useflash, $lo_name);
        if ($export_flash && !$export_html5)
        {
    		scorm2004_html_page_create($row['template_name'],$row['template_framework'], $rlo_file, $lo_name, $xml->getLanguage());
        }
        else
        {
            scorm2004_html5_page_create($row['template_framework'], $lo_name, $xml->getLanguage());
        }
	}else{
        if ($export_flash)
        {
		    basic_html_page_create($row['template_name'],$row['template_framework'], $rlo_file, $lo_name);
        }
        if ($export_html5)
        {
            basic_html5_page_create($row['template_framework'], $lo_name);
        }
	}
	
	/*
	 * Add the files to the zip file, create the archive, then send it to the user
	 */
	 
	xerte_zip_files($fullArchive, $dir_path);
	$zipfile->create_archive();
	$zipfile->download_file($row['zipname']);

	/*
	 * remove the files
	 */
	clean_up_files();
	unlink($dir_path . "template.xml");

?>