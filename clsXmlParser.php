//*********************************************************
//*********************************************************
//     XML Parser for Rentmater Inc   Created BY   Ivan Kavuma   3/1/2007
//********************************************************
//*********************************************************



<?php

class clsTestimonials
{
	var $TesDate;
	var $TesName;
	var $TesLoc;
    var $TesText;

}

class clsNewsItem
{
	var $NewsDate;
	var $NewsTitle;
	var $NewsText;

}

class clsText
{
    var $Home;
    var $About1;
    var $About2;
    var $ServiceRes;
    var $ServiceCom;
    var $ServiceBro;
    var $Career;
    var $History;
    var $Mission;
    var $Acquisition;
    var $Affiliations;
    
}
class clsGeneralSite
{
    var $SiteID;
    var $PropAddress;
    var $PropCity;
    var $PropState;
    var $PropZip;
    var $PropPhone;
    var $OfficeHours;
    var $MaintEmail;
    var $ContEmail;
    var $MailServer;
    var $UserName;
    var $Password;
    var $SiteName;
    var $SiteLink;
    var $Copyright;
    var $HasLogo;
    var $SiteText;
    var $SiteLogo;
    var $gPhoto;
    var $MapID;
    var $MetaDesc;
    var $MetaKeys;
    var $VTour;
    var $Utilities;
    var $PayRent;
    var $Featured;
    
}
class clsUser
{
	var $ID;
	var $Email;
	var $PassWord;
	var $Props;
}

class clsCareer
{	
	var $ID;
	var $Code;
	var $Category;
	var $Date;
	var $Title;
	Var $City;
	var $State;
	var $ShortDesc;
	var $Desc;
	var $Duties;
	var $Requirements;
	var $Conditions;
	var $Compensation;
	var $Benefits;
	var $Contact;
 

}
class clsExecutive
{
	var $Pic;
	var $Name;
	var $Title;
	var $Bio;
}

class clsUnit    // Unit class
{
    var $Name;
    var $Beds;
    var $Baths;
    var $Sqft;
    var $LowPrice;
    var $HighPrice;
    var $FloorPlan;
    var $VMoveIn;
}

class clsReport  //Reports Class
{
  var $ReportDate;
  var $Title;
  var $ReportFile;  //pdf path.

}

class clsProperty  //Properties
{   var $ID;
    var $Name;
    var $Address;
    var $City;
    var $State;
    var $Zip;
    var $Phone;
    var $Email;
    var $Maint;
    var $WebSite;
    var $Desc;
    var $Amenities = array();  // Future Expand to a collection.
    var $Highlights;
    var $Coupon;
    var $Vtour;
    var $MainPic;
    var $SubPics = array();   //collection of filenames
    var $BedLowPrices = array();   //collection of prices.
    var $Units = array();  //  UnitTypes   collection of units
    var $Reports = array();  //  ReportRoster  collection
    

//takes properties
function FindBeds($sBeds)
{
  //echo  " Var \$sBeds  ". $sBeds ."<br>";

        foreach( $this->Units as $Unit)
         {

            if ($Unit->Beds >= $sBeds)
            {
 //echo  $this->Name."  looking for Var \$sBeds:  ". $sBeds ." ==  Array Beds: " . $Unit->Beds ."<br>";

                return "Found" ;
            }


         }
return  "NOT Found"  ;



}

function FindRent($sRent)
{

         foreach( $this->Units as $Unit)
         {

            if (  $Unit->LowPrice <= $sRent )
            {
// echo  $this->Name."  looking for Var \$sRent:  ". $sRent ." ==  LowPrice : " . $Unit->LowPrice ."<br>";

                return "Found"    ;
            }
         }
return  "NOT Found" ;

}


}  //endProperty Class

//*********************************************************
//*********************************************************
//     MAIN CLASS   Created BY   IK   3/1/2007
//********************************************************
//*********************************************************

class clsXMLParser
{
      var $xml;
      var $Properties = array();   //an array of properties
      var $Executives = array(); 
      var $Careers = array(); 
      var $Users = array();
      var $GeneralSite;
      var $Text;
      var $NewsItems = array();
      var $Testimonials = array();
      
      

function GetProperty($ID)
{
    foreach($this->Properties as $Property)
    {
        if ($Property->ID == $ID)
        {
           return $Property ;
         }
    }
    return 0;
} //end GetProperty


 function BuildTestimonials($XmlNode)
 {

     $objTestimonials = new clsTestimonials();

                         foreach ($XmlNode->children() as $TestimonialsChild)
                        {
                               switch ($TestimonialsChild->getName())
                                {
                                case "TesDate":
                                        $objTestimonials->NewsDate = $TestimonialsChild;
                                         break;
                                case "TesName":
                                        $objTestimonials->TesName = $TestimonialsChild;
                                         break;
                                case "TesLoc":
                                        $objTestimonials->TesLoc = $TestimonialsChild;
                                         break;
                                case "TesText":
                                        $objTestimonials->TesText = $TestimonialsChild;
                                         break;
                               }

                          }

                   return $objTestimonials;


 }
      
function BuildNewsItems($XmlNode)
 {

     $objNewsItem = new clsNewsItem();

                         foreach ($XmlNode->children() as $NewsChild)
                        {
                               switch ($NewsChild->getName())
                                {
                                case "NewsDate":
                                        $objNewsItem->NewsDate = $NewsChild;
                                         break;
                                case "NewsTitle":
                                        $objNewsItem->NewsTitle = $NewsChild;
                                         break;
                                case "NewsText":
                                        $objNewsItem->NewsText = $NewsChild;
                                         break;
                               }

                          }

                   return $objNewsItem;


 }

          

 function BuildText($XmlNode)
 {

     $objText = new clsText();

                         foreach ($XmlNode->children() as $TextChild)
                        {
                               switch ($TextChild->getName())
                                {
                                case "Home":
                                        $objText->Home = $TextChild;
                                         break;
                                 case "About1":
                                        $objText->About1 = $TextChild;
                                         break;
                                case "About2":
                                        $objText->About2 = $TextChild;
                                         break;
                                case "ServiceRes":
                                        $objText->ServiceRes = $TextChild;
                                       // echo " ServiceRes: ". $objText->ServiceRes  ;
                                   break;
                                case "ServiceCom":
                                        $objText->ServiceCom = $TextChild;
                                       //  echo " ServiceCom: ". $objText->ServiceCom;
                                   break;
                                case "ServiceBro":
                                        $objText->ServiceBro = $TextChild;
                                       //  echo " ServiceCom: ". $objText->ServiceCom;
                                   break;

                                case "Career":
                                        $objText->Career = $TextChild;
                                         break;
                                case "History":
                                        $objText->History = $TextChild;
                                         break;
                                case "Mission":
                                        $objText->Mission = $TextChild;
                                         break;
                                case "Acquisition":
                                        $objText->Acquisition = $TextChild;
                                         break;
                                case "Affiliations":
                                        $objText->Affiliations = $TextChild;
                                         break;

                                 }

                          }

                   return $objText;


 }



 function BuildGeneralSite($XmlNode)
 {

     $objGeneralSite = new clsGeneralSite();

                         foreach ($XmlNode->children() as $GeneralSiteChild)
                        {

                                switch ($GeneralSiteChild->getName())
                                {
                                case "SiteID":
                                         $objGeneralSite->SiteID = $GeneralSiteChild;
                                         break;
                                case  "PropAddress":
                                        $objGeneralSite->PropAddress = $GeneralSiteChild;;
                                         break;
                                case  "PropCity":
                                       $objGeneralSite->PropCity = $GeneralSiteChild;;
                                         break;
                                case  "PropState":
                                      $objGeneralSite->PropState = $GeneralSiteChild;
                                         break;
                                case  "PropZip":
                                      $objGeneralSite->PropZip = $GeneralSiteChild;
                                         break;
                                case  "PropPhone" :
                                      $objGeneralSite->PropPhone = $GeneralSiteChild;
                                         break;
                                case  "OfficeHours" :
                                      $objGeneralSite->OfficeHours = $GeneralSiteChild;
                                         break;
                                case "MaintEmail":
                                     $objGeneralSite->MaintEmail = $GeneralSiteChild;
                                         break;
                                case  "ContEmail"   :
                                      $objGeneralSite->ContEmail = $GeneralSiteChild;
                                         break;
                                case  "MailServer"  :
                                      $objGeneralSite->MaintEmail = $GeneralSiteChild;
                                         break;
                                case "UserName":
                                     $objGeneralSite->UserName = $GeneralSiteChild;
                                         break;
                                case "Password" :
                                     $objGeneralSite->Password = $GeneralSiteChild;
                                         break;
                                case "SiteName"  :
                                     $objGeneralSite->SiteName = $GeneralSiteChild;
                                         break;
                                case "SiteLink"  :
                                     $objGeneralSite->SiteLink = $GeneralSiteChild;
                                         break;
                                case "Copyright"  :
                                     $objGeneralSite->Copyright = $GeneralSiteChild;
                                         break;
                                case  "HasLogo" :
                                      $objGeneralSite->HasLogo = $GeneralSiteChild;
                                         break;
                                case "SiteText" :
                                     $objGeneralSite->SiteText = $GeneralSiteChild;
                                         break;
                                case  "SiteLogo" :
                                     $objGeneralSite->SiteLogo = $GeneralSiteChild;
                                         break;
                                case "gPhoto" :
                                     $objGeneralSite->gPhoto = $GeneralSiteChild;
                                         break;
                                case "MapID" :
                                     $objGeneralSite->MapID = $GeneralSiteChild;
                                         break;
                                case  "MetaDesc":
                                      $objGeneralSite->MetaDesc =  $GeneralSiteChild;
                                         break;
                                case  "MetaKeys"  :
                                      $objGeneralSite->MetaKeys = $GeneralSiteChild;
                                         break;
                                case "VTour" :
                                     $objGeneralSite->VTour = $GeneralSiteChild;
                                         break;
                                case "Utilities" :
                                     $objGeneralSite->Utilities = $GeneralSiteChild;
                                         break;
                                case  "PayRent"  :
                                      $objGeneralSite->PayRent = $GeneralSiteChild;
                                         break;
                                case  "Featured":
                                   $objGeneralSite->Featured = $GeneralSiteChild;
                                         break;
                                

                                  }

                          }

                   return $objGeneralSite;
                   
                   
 }

  
  function BuildUsers($XmlNode)
 {
 
     $objUser = new clsUser();

                         foreach ($XmlNode->children() as $UserChild)
                        {
                              
                                switch ($UserChild->getName())
                                {
                                case "UsrID":
                                         $objUser->ID = $UserChild;
                                         break;
                                case "UsrEmail":
                                         $objUser->Email = $UserChild;
                                         break;
                                case "UsrPass":
                                         $objUser->PassWord = $UserChild;
                                         break;
                                case "UsrProps":
                                         $objUser->Props = $UserChild;
                                         break;
                                  }
                                  
                          }

                   return $objUser;
 }

  
 function BuildCareers($XmlNode)
 {
 
     $objCareer = new clsCareer();

                         foreach ($XmlNode->children() as $CareerChild)
                        {
                              
                                switch ($CareerChild->getName())
                                {
                                case "ID":
                                         $objCareer->ID = $CareerChild;
                                         break;
                                case "Code":
                                         $objCareer->Code = $CareerChild;
                                         break;
                                case "Category":
                                         $objCareer->Category = $CareerChild;
                                         break;
                                case "Date":
                                         $objCareer->Date = $CareerChild;
                                         break;
                                case "Title":
                                         $objCareer->Title = $CareerChild;
                                         break;
                                case "City":
                                         $objCareer->City = $CareerChild;
                                         break;
                               case "State":
                                         $objCareer->State = $CareerChild;
                                         break;
                               case "ShortDesc":
                                         $objCareer->ShortDesc = $CareerChild;
                                         break;
                               case "Desc":
                                         $objCareer->Desc = $CareerChild;
                                         break;
                               case "Duties":
                                         $objCareer->Duties = $CareerChild;
                                         break;
                               case "Requirements":
                                         $objCareer->Requirements = $CareerChild;
                                         break;
							   case "Conditions":
                                         $objCareer->Conditions = $CareerChild;
                                         break;
                               case "Compensation":
                                         $objCareer->Compensation = $CareerChild;
                                         break;
                               case "Benefits":
                                         $objCareer->Benefits = $CareerChild;
                                         break;
                               case "Affiliations":
                                         $objCareer->Affiliations = $CareerChild;
                                         break;
       
       
                               }
                               
                        }  
                        
               return $objCareer; 
 
 }    

 function GetJobCategories()
 {
     $Categories = array();


      foreach($this->Careers as $Job)
      {
             array_push($Categories,$Job->Category);
      }
     return array_unique($Categories);
     
}  //end GetJobCategories

function GetJobStates()
{

         $States = array();


         foreach($this->Careers as $Job)
         {
            array_push($States,$Job->State);
         }
         return array_unique($States);

}//end GetJobState

function GetPropStates()
{

         $States = array();


         foreach($this->Properties as $Property)
         {
            array_push($States,$Property->State);
         }
         return array_unique($States);

}//end GetPropState



function GetPropCities()
{

         $Cities = array();


         foreach($this->Properties as $Property)
         {
            array_push($Cities,$Property->City);
         }
         return array_unique($Cities);

}//end GetPropState




 function BuildExecutives($XmlNode)
 {
 
  $objExec = new clsExecutive();

                         foreach ($XmlNode->children() as $ExecChild)
                        {
                                switch ($ExecChild->getName())
                                {
                                case "ExecPic":
                                         $objExec->Pic = $ExecChild;
                                         break;
                                case "ExecName":
                                         $objExec->Name = $ExecChild;
                                         break;
                                case "ExecTitle":
                                         $objExec->Title = $ExecChild;
                                         break;
                                case "ExecBio":
                                         $objExec->Bio = $ExecChild;
                                         break;
                         
                               }
                               
                        }  
                        
                return $objExec; 
 
 }
      

 function BuildReport($ArrayReports,$ReportXMLNode)
 {
          foreach ($ReportXMLNode->childen as $ReportChild)
          {

                 If ( $ReportChild->getName() == "Report")
                {
                     $objReport = new clsReport();

                  foreach (  $ReportChild->children()  as $RPChild)
                   {

                        switch ($RPChild->getName())
                        {
                        case "Date" :

                             $objReport->ReportDate = $RPChild ;
                             break;
                        case "Title" :

                             $objReport->Title = $RPChild ;
                             break;
                        case "File" :

                             $objReport->ReportFile = $RPChild ;
                             break;
                         }
                      }

                         array_push($ArrayReports,$objReport)   ;
                }

            }

            return $ArrayUnits  ;

}

    function    BuildUnit($ArrayUnits,$UnitXMLNode)
    {

            foreach ($UnitXMLNode->children() as $UnitTypeChild)
            {


                If ( $UnitTypeChild->getName() == "Unit")
                {
                     $objUnit = new clsUnit();
                         
                    foreach (  $UnitTypeChild->children()  as $UnitChild)
                   {


                         switch ($UnitChild->getName())
                        {
                        case "UnitName" :

                             $objUnit->Name = $UnitChild ;

                             
                             break;
                        case "UnitBeds"  :

                             $objUnit->Beds = $UnitChild ;
                           //  echo "<br> Unit Beds  " .$UnitChild->getName()."  -->  ".$UnitChild ;
                             break;
                        case "UnitBaths" :

                             $objUnit->Baths = $UnitChild ;
                             break;
                        case "UnitSqFt" :

                             $objUnit->Sqft = $UnitChild ;
                             break;
                        case "UnitPriceLow" :

                             $objUnit->LowPrice = $UnitChild ;
                             break;
                        case "UnitPriceHigh" :

                             $objUnit->HighPrice = $UnitChild ;
                             break;
                        case "UnitName"  :

                             $objUnit->Name = $UnitChild ;
                             break;
                        case "FloorPlan"  :

                             $objUnit->FloorPlan = $UnitChild ;
                             break;
                        case "VMoveIn" :

                             $objUnit->VMoveIn = $UnitChild ;
                             break;

                        }


                     }
                     
                     // echo "<br> Unit Beds  " ."  -->  " .$objUnit->Beds;
                         array_push($ArrayUnits,$objUnit)   ;
                }

            }

            return $ArrayUnits  ;
    }



      
      
      
         //propNode is from xml while objProp is a custom class
         
       //
       function BuildProperty($PropNode)
      {
               $objProp = new clsProperty();

                         foreach ($PropNode->children() as $PropChild)
                        {
                                switch ($PropChild->getName())
                                {
                                case "ID":
                                         $objProp->ID = $PropChild;
                                         break;
                                case "Name":
                                         $objProp->Name = $PropChild;
                                         break;
                                case "Address":
                                         $objProp->Address = $PropChild;
                                         break;
                                case "City":
                                         $objProp->City = $PropChild;
                                         break;
                                case "State":
                                         $objProp->State = $PropChild;
                                         break;
                                case "Zip":
                                         $objProp->Zip = $PropChild;
                                         break;
                                case "Phone":
                                         $objProp->Phone = $PropChild;
                                         break;
                                case "Email" :
                                         $objProp->Email = $PropChild;
                                         break;
                                case "Maint":
                                         $objProp->Maint = $PropChild;
                                         break;
                                case "WebSite":
                                         $objProp->WebSite = $PropChild;
                                         break;
                                case "Desc" :
                                         $objProp->Desc = $PropChild;
                                         break;
                                case "Amenities":
                                         $objProp->Amenities = explode("--",$PropChild);
                                         break;
                                case "Highlights" :
                                         $objProp->Highlights = $PropChild;
                                         break;
                                case "Coupon"  :
                                         $objProp->Coupon = $PropChild;
                                         break;
                                case "Vtour" :
                                         $objProp->Vtour = $PropChild;
                                         break;
                                case "MainPic" :
                                         $objProp->MainPic = $PropChild;
                                         break;
                                case "SubPic1" :
                                case "SubPic2" :
                                case "SubPic3" :
                                case "SubPic4" :
                                case "SubPic5" :
                                         array_push($objProp->SubPics, $PropChild)  ;
                                         break;
                                case "OneBedLowPrices" :
                                case "TwoBedLowPrices"  :
                                case "ThreeBedLowPrices" :
                                case "FourBedLowPrices" :

                                         array_push($objProp->BedLowPrices,$PropChild)  ;
                                         //  or die(" Error Creating BedLowPrices object ");
                                         break;

                                case "UnitTypes"  :

                                           $objProp->Units = $this->BuildUnit($objProp->Units,$PropChild);
                                        // die(" Error Creating Unit object ") ;

                                         break;
                                case "ReportRoster" :
                                

                                       $objProp->Reports =  $this->BuildReport($objProp->Reports,$PropChild) ;
                                     // or die (" Error Creating Report object ")   ;
                                         break;
                                }

                        }

                        return  $objProp;


    }

      function CreateProperties($FileName)
      {
      
           libxml_use_internal_errors (TRUE);
           



          //Origin
           if($this->xml = simplexml_load_file($FileName))
           {


               foreach($this->xml->children() as $PropNode)
               {
               
                   if ($PropNode->getName() == "Property")
                   {

                      array_push($this->Properties,$this->BuildProperty($PropNode));

                   }
    
    
               } //end foreach
               

               
          }  //end if
          else
          {

              $this->SendErrors();

          }
          
          
        //    unlink($FileName.".tmp");
          
       }  // CreateProperties


 function SendErrors()
 {

               $this->CreateGeneralSite("data/Site.xml")  ;
               $SiteID = $this->GeneralSite->SiteID;
              /* Get all errors as an array, loop through them, and dump the output */
               if($lasterror = libxml_get_last_error())
               {
               echo "<br>";

                        $totalerror ;
                     foreach($lasterror as $tmperror)
                     {

                       //  echo    $tmperror;
                       $totalerror = $totalerror.$tmperror;

                     }

                              $from = "CustomerService@rentmatters.com";
                              $cc   = "mhatipovic@rentmatters.com";
                              $headers = "From: $from\r\n CC: $cc\r\n ";

                      mail("ikavuma@rentmatters.com"
                             , "ERROR: in CreateProperties-the:". date("l dS \of F Y h:i:s A")." From the XML file
                             \r\n ". $totalerror."  SiteID : ".$SiteID
                             ," For message look at the subject"
                             ,$headers);

                       echo "<center><b>An error occurred reading the xml. <br>
                                   The Web Administrator has been notified. We will correct it as soon as posible. <br>
                                   We appologize for any inconvenience caused.<br> </center>
                                    </b>";


                }

 } //Send errors.


function CreateExecutives($FileName)
{
libxml_use_internal_errors (TRUE);
           

          //Origin
           if($this->xml = simplexml_load_file($FileName))
           {


               foreach($this->xml->children() as $ExecNode)
               {
               
                   if ($ExecNode->getName() == "Executive")
                   {

                      array_push($this->Executives,$this->BuildExecutives($ExecNode));

                   }
    
    
               } //end foreach
               

               
          }  //end if
          else
          {


                       $this->SendErrors();
          }
          
          
          //  unlink($FileName.".tmp");
          
       }  // CreateExecutives
       
       
       
       
       
       
function CreateCareers($FileName)
{
libxml_use_internal_errors (TRUE);
           

          //Origin
           if($this->xml = simplexml_load_file($FileName))
           {


               foreach($this->xml->children() as $CareerNode)
               {
               
                   if ($CareerNode->getName() == "Job")
                   {

                      array_push($this->Careers,$this->BuildCareers($CareerNode));

                   }
    
    
               } //end foreach
               

               
          }  //end if
          else
          {


                           $this->SendErrors();
          }
          
          
         //   unlink($FileName.".tmp");
          
       }  // CreateCareers



function CreateUsers($FileName)
{
libxml_use_internal_errors (TRUE);
           
  
          //Origin
           if($this->xml = simplexml_load_file($FileName))
           {


               foreach($this->xml->children() as $UserNode)
               {
               
                   if ($UserNode->getName() == "User")
                   {

                      array_push($this->Users,$this->BuildUsers($UserNode));

                   }
    
    
               } //end foreach
               

               
          }  //end if
          else
          {
              $this->SendErrors();
          }
          
          
          //  unlink($FileName.".tmp");
          
       }  // CreateCareers


function CreateGeneralSite($FileName)
{
libxml_use_internal_errors (TRUE);


          //Origin
           if($this->xml = simplexml_load_file($FileName))
           {


               foreach($this->xml->children() as $GeneralSiteNode)
               {

                   if ($GeneralSiteNode->getName() == "General")
                   {

                      $this->GeneralSite = $this->BuildGeneralSite($GeneralSiteNode);

                   }


               } //end foreach



          }  //end if
          else
          {
              $this->SendErrors();
          }




       }  // CreateGeneralSite



function CreateText($FileName)
{
libxml_use_internal_errors (TRUE);


          //Origin
           if($this->xml = simplexml_load_file($FileName))
           {

                   if ($this->xml->getName() == "Texts")
                   {
                      $this->Text = $this->BuildText($this->xml);

                   }

          }  //end if
          else
          {

                        $this->SendErrors();

          }




       }  // CreateText



function CreateNewsItems($FileName)
{
libxml_use_internal_errors (TRUE);


          //Origin
           if($this->xml = simplexml_load_file($FileName))
           {


               foreach($this->xml->children() as $NewsItemsNode)
               {

                   if ($NewsItemsNode->getName() == "NewsItem")
                   {

                      array_push($this->NewsItems,$this->BuildNewsItems($NewsItemsNode));

                   }


               } //end foreach



          }  //end if
          else
          {

              $this->SendErrors();
          }


          //  unlink($FileName.".tmp");

       }  // CreateNewsItems




function CreateTestimonials($FileName)
{
libxml_use_internal_errors (TRUE);


          //Origin
           if($this->xml = simplexml_load_file($FileName))
           {


               foreach($this->xml->children() as $TestimonialNode)
               {

                   if ($TestimonialNode->getName() == "Testimonial")
                   {

                      array_push($this->Testimonials,$this->BuildTestimonials($TestimonialNode));

                   }


               } //end foreach



          }  //end if
          else
          {
              $this->SendErrors();
          }


          //  unlink($FileName.".tmp");

       }  // CreateTestimonials



}//end class

?>


