# DCO_extractIssueXML
Extract single files from issue.xml created from the Native XML Plugin in OJS 3.x.
See https://docs.pkp.sfu.ca/admin-guide/en/data-import-and-export.

The file structure
```
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
```
Due to limitation of filesize on github there is only one XML-file as example. 
Usually the DCO issue files are up to 300 MB.

## dco_urls.xml
Own XML with all published DCO issues.
