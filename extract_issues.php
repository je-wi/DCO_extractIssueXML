<?php
/*
    Universität Leipzig, Lehrstuhl für Alte Geschichte, Leipzig 2020  
    # GPLv3 copyrigth
    # This program is free software: you can redistribute it and/or modify
    # it under the terms of the GNU General Public License as published by
    # the Free Software Foundation, either version 3 of the License, or
    # (at your option) any later version.
    # This program is distributed in the hope that it will be useful,
    # but WITHOUT ANY WARRANTY; without even the implied warranty of
    # MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    # GNU General Public License for more details.
    # You should have received a copy of the GNU General Public License
    # along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/


# Extract dco-articles from XML-issue-file, created from Native XML Plugin in OJS.
# See https://docs.pkp.sfu.ca/admin-guide/en/data-import-and-export.

# Due to limitation of filesize on github there is only one XML-file as source.
# 
# The file structure
/*
extracted/
  Bd. 4,1 (2018)/
    47289/
      41867_153991_DCO 4,1 (2018) Schubert.pdf
      48489.xml
    48490/
      41863_153984_DCO 4,1 (2018) KrämerF.pdf
      48490.xml
    47289/
      41868_153992_Raunig DCO (2018).pdf  
      47289.xml
    48491/  
      41870_153994_DCO 4,1 (2018) Rehbein.pdf
      48491.xml
    48492/
      41866_153990_DCO 4,1 (2018) Schilz.pdf  
      48492.xml
    48493/  
      41865_153988_DCO 4,1 (2018) Bubenhofer F.pdf 
      48493.xml
  cover_issue_3911_de_DE.jpg
  3911-784-PB.pdf
source/
  dco_4_1.xml
*/


/*------------------------------------------------------------------------------
                     starts here
------------------------------------------------------------------------------*/
error_reporting(-1);

$dir2files = 'extracted';
if(!is_dir($dir2files))
  {
  mkdir($dir2files);
  }

$dir = "source/";
$files = readDocFolder($dir);


foreach($files as $file)
  {
  if( strpos($file, ".xml") !== false AND strpos($file, "dco") !== false )
    {

    	$document = new DOMDocument();
    	$xml = file_get_contents($dir.$file);
	    $document->loadXML($xml);

      # band
      $dco_bd = "";
      $issue_id = $document->getElementsByTagName('issue_identification')[0];
      foreach($issue_id->childNodes as $child)
        {
        if( $child->nodeName == 'title' ) $dco_bd=$child->nodeValue;
        }

      if( !empty($dco_bd)  )
        {
        $dir2bd = $dir2files."/".$dco_bd;
        if( !is_dir($dir2bd) ) mkdir($dir2bd);

        }
      else
        {
        echo $file." error<br>";
        break;
        }



      # cover
      $covernodes = $document->getElementsByTagName('cover');
    	foreach($covernodes as $cover)
    	  {
        $coverfilename = $cover->getElementsByTagName('cover_image')[0]->textContent;
        $base64 = $cover->getElementsByTagName('embed')[0]->textContent;
        $bin = base64_decode($base64);
          
file_put_contents($dir2bd."/".$coverfilename, $bin);
echo $dir2bd."/".$coverfilename.'<br>';

    	  }

      # issue file
      $issue_file = $document->getElementsByTagName('issue_file');
    	foreach($issue_file as $issue)
    	  {
        $filename = $issue->getElementsByTagName('file_name')[0]->textContent;
        $base64 = $issue->getElementsByTagName('embed')[0]->textContent;
        $bin = base64_decode($base64);
        
file_put_contents($dir2bd."/".$filename, $bin);
echo $dir2bd."/".$filename.'<br>';  
        
        }

      # articles
      $articles = $document->getElementsByTagName('article');
      foreach($articles as $article)
        {
        $article_id = "unknown";
        foreach($article->childNodes as $child)
          {
          if( $child->nodeName == "id" AND $child->getAttribute("type")=="internal" )
            {
            $article_id = $child->nodeValue;
            if( !is_dir($dir2bd."/".$article_id) ) mkdir($dir2bd."/".$article_id);
            break;
            }
          }

        $submission_file_ref = $article->getElementsByTagName('article_galley')[0]->getElementsByTagName('submission_file_ref')[0]->getAttribute('id');
        $submission_file_id = $submission_file_ref;
        foreach($article->getElementsByTagName('article_galley')[0]->getElementsByTagName('id') AS $child )
          {
          if( $child->getAttribute("type")=="internal" ) $submission_file_id = $child->nodeValue.'_'.$submission_file_ref;
          }

        
        $submission_files = $article->getElementsByTagName('submission_file');
        foreach($submission_files as $sf)
          {
          if( $sf->getAttribute('id')==$submission_file_ref )
            {
            $name = $sf->getElementsByTagName('revision')[0]->getAttribute('filename');
            $filename = $submission_file_id.'_'.$name;
            $base64 = $sf->getElementsByTagName('embed')[0]->textContent;
            $bin = base64_decode($base64);
            
file_put_contents($dir2bd."/".$article_id."/".$filename, $bin);
echo $dir2bd."/".$article_id."/".$filename.'<br>'; 
            }
          }//end foreach


        # suplementary files
        $supp_files = $article->getElementsByTagName('supplementary_file');
        foreach($supp_files as $sf)
          {
          $filename = $sf->getElementsByTagName('revision')[0]->getAttribute('filename');
          $base64 = $sf->getElementsByTagName('embed')[0]->textContent;
          $bin = base64_decode($base64);

file_put_contents($dir2bd."/".$article_id."/".$filename, $bin);
echo $dir2bd."/".$article_id."/".$filename.'<br>'; 

          }

        # article as xml
        $dom_document = new DOMDocument('1.0');
        $dom_document->formatOutput = true;  
        $dom_document->loadXML("<root></root>");
        $dom_document->saveXML();

        $import = $dom_document->importNode($article, true);
        $dom_document->documentElement->appendChild($import);
                
        $article_xml = $dom_document->saveXML();        


file_put_contents($dir2bd."/".$article_id."/".$article_id.".xml", $article_xml);  
echo $dir2bd."/".$article_id."/".$article_id.".xml<br>"; 

       
        }//end foreach articles

    }//end if

  }//end foreach files


/*------------------------------------------------------------------------------
                     ends here
------------------------------------------------------------------------------*/



/*------------------------------------------------------------------------------
                     some helper functions - begin
------------------------------------------------------------------------------*/

function readDocFolder($dir)
  {
  $files = scandir($dir);
  $r = array();
  
  if( is_array($files) )
    {
    foreach ($files as $key => $value)
       {
          if ( !in_array($value,array(".","..")) AND !is_dir($dir.'/'.$value) )
          {
          $r[] = $value;
          }
       } 
    
    }
  return $r;  
  } 
  
/*------------------------------------------------------------------------------
                     some helper functions - end
------------------------------------------------------------------------------*/        
  
?>
