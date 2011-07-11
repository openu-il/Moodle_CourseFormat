/**
 * Contains Main class and supporting functions for ajax course layout
 *
 * $Id: ajaxcourse.js,v 1.9.6.8 2009/11/21 16:29:06 skodak Exp $
 */


//hide content body until done loading (manipulation looks ugly elsewise)
//document.getElementById('content').style.display = 'none';

// If firebug console is undefined, define a fake one here
if (window.console) {
    console.vardump = function(data) {
        retval = '';
        for (key in data) {
            retval += key+' = '+data[key] + "\n";
        }
        console.log(retval);
    };
}

//onload object for handling scripts on page load, this insures they run in my order
function onload_class_ouil() {
    this.scripts = new Array();
    this.debug = true;
}


onload_class_ouil.prototype.add = function(script) {
    if (this.debug) {
        YAHOO.log("onloadobj.add - adding "+script, "junk");
    }
    this.scripts[this.scripts.length] = script;
}


onload_class_ouil.prototype.load = function() {
    var scriptcount = this.scripts.length;
    if (this.debug) {
        YAHOO.log("onloadobj.load - loading "+scriptcount+" scripts", "info");
    }
    for (i=0; i<scriptcount; i++) {
        eval(this.scripts[i]);
    }
}


var onloadobj_ouil = new onload_class_ouil();


//main page object
function main_class_ouil() {
    this.debug = true;
    this.portal = new php_portal_class();

    this.blocks = new Array();
    this.sections = new Array();
    this.sectiondates = {};
    this.leftcolumn = null;
    this.rightcolumn = null;
    this.adminBlock = null;
    this.tempBlock = null;
    this.icons = [];
    this.marker = null;
    this.courseformat = null;
    this.numsections = null;

    //things to process onload
    onloadobj_ouil.add('main.process_document();');
    onloadobj_ouil.add("document.getElementById('content').style.display='block';");

    //connection queue allows xhttp requests to be sent in order
    this.connectQueue = [];
    this.connectQueueHead = 0;
    this.connectQueueConnection = null;
}


main_class_ouil.prototype.process_blocks = function() {
    //remove unneeded icons (old school position icons and delete/hide
    //although they will be read)
    var rmIconClasses = ['icon up', 'icon down', 'icon right', 'icon left', 'icon delete', 'icon hide'];
    for (var c=0; c<rmIconClasses.length; c++) {
        els = YAHOO.util.Dom.getElementsByClassName(rmIconClasses[c]);

        for (var x=0; x<els.length; x++) {
            els[x].parentNode.removeChild(els[x]);
        }
    }    
    //process the block ids passed from php
    var blockcount = this.portal.blocks.length;
    YAHOO.log("main.processBlocks - processing "+blockcount+" blocks", "info");

    for (i=0; i<blockcount; i++) {
        this.blocks[i] = new block_class(this.portal.blocks[i][1], "blocks");

        //put in correct side array also
        if (this.portal.blocks[i][0] == 'l') {
            main.leftcolumn.add_block(this.blocks[i]);
        } else if (this.portal.blocks[i][0] == 'r') {
            main.rightcolumn.add_block(this.blocks[i]);  
        }

        //hide if called for
        if (this.portal.blocks[i][2] == 1) {
            this.blocks[i].toggle_hide(null, null, true);
        }
    }     
}


main_class_ouil.prototype.process_document = function() {
    //process the document to get important containers
    YAHOO.log("Processing Document", "info");

    //process columns for blocks
    this.leftcolumn = new column_class('left-column', "blocks", null, 'l');
    this.rightcolumn = new column_class('right-column', "blocks", null, 'r');


	//yifatsh openu 1/2011 in courseFormat ouil do not porcess the section on int page

    //process sections    
	if(this.getString('courseformat', this.sectionId) != "ouil"){
		    this.courseformat = this.portal.courseformat;
    var maxct = this.portal.numsections;      
    for (var ct=0; ct <= maxct; ct++) {
        if(document.getElementById('section-'+ct) != null) {  
            this.sections[ct] = new section_class('section-'+ct, "sections", null, ct!=0?true:false);
            this.sections[ct].addToGroup('resources');
            if (ct > 0) {
                var sectiontitle = YAHOO.util.Selector.query('#section-'+ct+' h3.weekdates')[0];
                if (undefined !== sectiontitle) { // Only save date for weekly format
                    this.sectiondates[ct] = sectiontitle.innerHTML;
                }
            }
        }     
    }
    if (this.debug) {
        YAHOO.log("Processed "+ct+" sections");
    }
	}//couseFormat not ouil


    this.adminBlock = YAHOO.util.Dom.getElementsByClassName('block_adminblock')[0];
    this.tempBlock = YAHOO.util.Dom.getElementsByClassName('tempblockhandler')[0];
    
    YAHOO.log("admin - "+this.adminBlock.className);
}

/**
yifatsh openu 1/2011
open single  init buttons in the sections
**/
main_class_ouil.prototype.process_single_sections = function(sectionid) {
 //process sections
     var maxct =sectionid;
 if(sectionid==0){
   maxct = this.portal.numsections;
 }
    this.courseformat = this.portal.courseformat;

    for (var ct=sectionid; ct <= maxct; ct++) {
        if(document.getElementById('section-'+ct) != null) {

			if(this.sections[ct]==null){
				this.sections[ct] = new section_class('section-'+ct, "sections", null, ct!=0?true:false);
				this.sections[ct].addToGroup('resources');
				if (ct > 0) {
					var sectiontitle = YAHOO.util.Selector.query('#section-'+ct+' h3.weekdates')[0];
					if (undefined !== sectiontitle) { // Only save date for weekly format
						this.sectiondates[ct] = sectiontitle.innerHTML;
					}
				}
			}//section ==null
        }
    }
    if (this.debug) {
        YAHOO.log("Processed "+ct+" sections");
    }

}




main_class_ouil.prototype.mk_safe_for_transport = function(input) {
    return input.replace(/&/i, '_.amp._');
} 


//return block by id
main_class_ouil.prototype.get_block_index = function(el) {
    var blockcount = this.blocks.length;
    for (i=0; i<blockcount; i++) {
        if (this.blocks[i] == el) {
            return i;
        }
    }
}


main_class_ouil.prototype.get_section_index = function(el) {
    var sectioncount = this.sections.length;
    for (i=0; i<sectioncount; i++) {
        if (this.sections[i] == el) {
            return i;
        }
    }
}

main_class_ouil.prototype.mk_button = function(tag, imgSrc, text, attributes, imgAttributes) {
    //Create button and return object.
    //Set the text: the container TITLE or image ALT attributes can be overridden, eg.
    //  main.mk_button('a', '/i/move_2d.gif', strmove, [['title', strmoveshort]]);
    var container = document.createElement(tag);
    container.style.cursor = 'pointer';
    container.setAttribute('title', text);
    var image = document.createElement('img');

    image.setAttribute('src', main_ouil.portal.strings['pixpath']+imgSrc);
    image.setAttribute('alt', text);
    //image.setAttribute('title', '');
    container.appendChild(image);

    if (attributes != null) {
        for (var c=0; c<attributes.length; c++) {
            if (attributes[c][0] == 'title' && this.is_ie()) {
                image.setAttribute(attributes[c][0], attributes[c][1]); //IE hack: transfer 'title'.
            } else {
                container.setAttribute(attributes[c][0], attributes[c][1]);
            }
        }
    }
    if (imgAttributes != null) {
        for (var c=0; c<imgAttributes.length; c++) {
            image.setAttribute(imgAttributes[c][0], imgAttributes[c][1]);
        }
    }
    image.setAttribute('hspace', '3');
    return container;
}


main_class_ouil.prototype.connect = function(method, urlStub, callback, body) {
    if (this.debug) {
        YAHOO.log("Making "+method+" connection to /course/rest.php?courseId="+main_ouil.portal.id+"&"+urlStub);
    }
    if (callback == null) {
        if (this.debug) {
            callback = {
                success: function(response) {
                    YAHOO.log("Response from the Request: " + response.statusText + ": " + response.responseText, 'info');
                },
                failure: function() {
                    YAHOO.log("Response from the Request: " + response.statusText + ": " + response.responseText, 'error');
                }
            };
        } else {
            callback = {};
        }
    }
    return YAHOO.util.Connect.asyncRequest(method, this.portal.strings['wwwroot']+"/course/rest.php?courseId="+main_ouil.portal.id+"&sesskey="+this.portal.strings['sesskey']+"&"+urlStub, callback, body);
}


main_class_ouil.prototype.connectQueue_add = function(method, urlStub, callback, body) {
    var Qlength = main_ouil.connectQueue.length;
    main_ouil.connectQueue[Qlength] = [];
    main_ouil.connectQueue[Qlength]['method'] = method;
    main_ouil.connectQueue[Qlength]['urlStub'] = urlStub;
    main_ouil.connectQueue[Qlength]['body'] = body;

    if (main_ouil.connectQueueConnection == null || !YAHOO.util.Connect.isCallInProgress(main_ouil.connectQueueConnection)) {
        main_ouil.connectQueue_fireNext();
    }
}


main_class_ouil.prototype.connectQueue_fireNext = function() {
    var head = main_ouil.connectQueueHead;
    if (head >= main_ouil.connectQueue.length) {
        return;
    }
    var callback = {
    success: function(){
             main_ouil.connectQueue_fireNext();
        }
    }
    main_ouil.connectQueueConnection = main_ouil.connect(main_ouil.connectQueue[head]['method'],
            main_ouil.connectQueue[head]['urlStub'],
            callback,
            main_ouil.connectQueue[head]['body']);
    main_ouil.connectQueueHead++;
}


main_class_ouil.prototype.update_marker = function(newMarker) {
    if (this.marker != null) {
        this.marker.toggle_highlight();
    }
    this.marker = newMarker;     
    this.marker.toggle_highlight();
	alert (this.marker.sectionId);
    this.connect('post', 'class=course&field=marker', null, 'value='+this.marker.sectionId);
}


main_class_ouil.prototype.getString = function(identifier, variable) {
    if (this.portal.strings[identifier]) {
        return this.portal.strings[identifier].replace(/_var_/, variable);
    }
}

main_class_ouil.prototype.is_ie = function() {
    var agent = navigator.userAgent.toLowerCase();
    if ((agent.indexOf('msie') != -1)) {
        return true;
    }
    return false;
}


var main_ouil = new main_class_ouil();


function php_portal_class() {
    //portal to php data
    this.id = null;
    this.debug = null;

    //array of id's of blocks set at end of page load by php    
    this.blocks = new Array();
    this.imagePath = null;

    //flag for week fomat
    this.isWeek = false;

    //strings    
    this.strings = [];

    YAHOO.log("Instantiated php_portal_class", "info");
}
