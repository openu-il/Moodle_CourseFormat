

<?php
global  $useajax;
if($useajax===true)
{
    require_js($CFG->wwwroot.'/course/format/ouil/ajaxcourse.js');
    require_js($CFG->wwwroot.'/course/format/ouil/block_classes.js');
    require_js($CFG->wwwroot.'/course/format/ouil/section_classes.js');
}
?>


<script type="text/javascript">
<?php

echo ' var closeSection_title="open All section"; ' ;
echo ' var openSection_title="close All section" ;' ;

echo "var cssNode = document.createElement('link');";
echo "cssNode.setAttribute('rel', 'stylesheet');";
echo "cssNode.setAttribute('type', 'text/css');";

echo "cssNode.setAttribute('href', '".$CFG->wwwroot."/course/format/ouil/js-override-topcoll.css');\n";
//echo "cssNode.setAttribute('href', '".$CFG->themewww .'/'. current_theme()."/js-override-topcoll.css');\n";
echo "document.getElementsByTagName('head')[0].appendChild(cssNode);";
echo "var displaysection= $displaysection;";
$markersection=$course->marker;
echo "var markersection= 'section-'+$markersection;";


echo ' var closeSection_title="'.get_string('closeSectionTxt','format_ouil').'"; ' ;
echo ' var openSection_title="'.get_string('openSectionTxt','format_ouil').'"; ' ;

echo "function toggle_topic(toggler)
      {
		if(document.getElementById)
		{

			imageSwitch = toggler;
			targetElement = toggler.parentNode.parentNode.nextSibling; // Called from a <td> inside a <tr> so find the next <tr>.
	
			if(targetElement.className == undefined)
			{
				targetElement = toggler.parentNode.parentNode.nextSibling.nextSibling; // If not found, try the next.
			}
		
			if (navigator.userAgent.indexOf('IE')!= -1)
			{
				var displaySetting = \"block\";
			}
			else
			{
				var displaySetting = \"table-row\";
			}


	if((displaysection!=0) ||(targetElement.id==markersection)){
		if(targetElement.style.display.length==0)  	targetElement.style.display=displaySetting;
	}

		if (targetElement.style.display == displaySetting)
		{
			targetElement.style.display = \"none\";
			imageSwitch.className='openSection';
			imageSwitch.title=openSection_title;
		}
		else
		{";

		if($useajax===true){
		echo " 
			sectionID=targetElement.id;
			if(sectionID.indexOf('section-')!=-1){
				sectionID=sectionID.substring(8);
				if(main_ouil){
					main_ouil.process_single_sections (sectionID);
				}
			}
			";
		}
			echo "
			targetElement.style.display = displaySetting;
			imageSwitch.className='closeSection';
			imageSwitch.title=closeSection_title;
			}
	}
}";




echo ' var openAllSection_gif= "'.$CFG->pixpath .'/t/down.gif"; ';
echo ' var closeAllSection_gif= "'.$CFG->pixpath .'/t/up.gif";';


echo ' var closeAllSection_title="'.get_string('closeAllSectionTxt','format_ouil').'"; ' ;
echo ' var openAllSection_title="'.get_string('openAllSectionTxt','format_ouil').'"; ' ;

echo 'function SetChecked(status)
	{';
if($useajax===true){
echo "
	if(	main_ouil!=undefined){
		main_ouil.process_single_sections(0);
	} ";
}
	echo '
	var actionAllSection=document.getElementById("actionOnallSection");	
	var actionOnallsectionIMG=document.getElementById("actionOnallsectionIMG");	
	if (actionAllSection.className.indexOf("setAllsectionOpen")!=-1){
		status=true;
	}else{
		status=false;
	}
	var name_tmp="";
	var displaySetting ="none";
	var class_name="openSection";
	var  actionAllSection_class_name="setAllsectionOpen"
	if(status){
			actionOnallsectionIMG.src=closeAllSection_gif;
			actionOnallsectionIMG.title=closeAllSection_title;
			 class_name="closeSection";
			 actionAllSection_class_name="setAllsectionClose"
			if (navigator.userAgent.indexOf("IE")!= -1){
				displaySetting = "block";
			}
			else{
				displaySetting = "table-row";
			}
	}else{
		actionOnallsectionIMG.src=openAllSection_gif;
		actionOnallsectionIMG.title=openAllSection_title;
	}
	//status
	var toggleIndex=0;
	var n=1
	objectID="section-1";
	var	targetElement=document.getElementById(objectID);
	while  (targetElement!=null){
			targetElement.style.display = displaySetting;
			toggleID="toggle-"+n;
			var imageSwitch=document.getElementById(toggleID);
			imageSwitch.className=class_name;
		n++;
		objectID="section-"+n;
		targetElement=document.getElementById(objectID);
	}//for
		actionAllSection.className=actionAllSection_class_name;

}';


echo 'function setIFRAM(IFRAMEID,url , hideobj1,hideobj2){
		var	targetElement=document.getElementById(IFRAMEID);
		if(targetElement!=null){
			targetElement.style.display="block";
			targetElement.src=url;
		}
		if(hideobj1.length>0){
			targetElement=document.getElementById(hideobj1);
			if(targetElement!=null){
				targetElement.style.display="none";
			}
		}
		if(hideobj2.length>0){
			targetElement=document.getElementById(hideobj2);
			if(targetElement!=null){
				targetElement.style.display="none";
			}
		}


}';

?>
</script>
