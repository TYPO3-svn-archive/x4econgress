/*!
 * Ext JS Library 3.2.1
 * Copyright(c) 2006-2010 Ext JS, Inc.
 * licensing@extjs.com
 * http://www.extjs.com/license
 */
 
 
 function nodeSortRequest(parent,node){
 
 	reqUrl = location.href;
 	reqUrl += '?action=sort';
 	reqUrl += '&parentUid='+parent.id;
 	reqUrl += '&childUid='+node.id;
 	reqUrl += '&eID=congressSort';
 	reqUrl += '&pid='+actSysFolder;
 
 	Ext.Ajax.request({
		url: reqUrl,
		failure: function(){
			Ext.Msg.alert('Status', 'Changes not saved.');
		}
	});
 }
 
var TreeTest = function(){
    // shorthand
    var Tree = Ext.tree;
    
    return {
        init : function(){
        
            // yui-ext tree
            var tree = new Tree.TreePanel({
                animate:true, 
                autoScroll:true,
                loader: new Tree.TreeLoader({dataUrl: location.href+'?eID=congressSort&type=proposals&pid='+actSysFolder}),
                enableDD:true,
                rootVisible: false,
                containerScroll: true,
                border: false,
                width: 370,
                dropConfig: {appendOnly:true},
                listeners: {
                	nodedrop: function(drop){
                		nodeSortRequest(drop.target,drop.dropNode);
                	}
                		
                }
            });
            
            // add a tree sorter in folder mode
            // new Tree.TreeSorter(tree, {folderSort:true});
            
            // set the root node
            var root = new Tree.AsyncTreeNode({
                text: 'Proposals', 
                draggable:false, // disable root node dragging
                id:'src'
            });
            tree.setRootNode(root);
            
            // render the tree
            tree.render('proposals');
            
            root.expand(true);
            
            //-------------------------------------------------------------
            
            // ExtJS tree            
            var tree2 = new Tree.TreePanel({
                animate:true,
                autoScroll:true,
                rootVisible: false,
                loader: new Ext.tree.TreeLoader({dataUrl: location.href+'?eID=congressSort&type=assigned&pid='+actSysFolder}),
                containerScroll: true,
                border: false,
                width: 370,
                enableDD:true,
                dropConfig: {appendOnly:true},
                listeners: {
                	nodedrop: function(drop){
                		nodeSortRequest(drop.target,drop.dropNode);
                	}
                		
                }
            });
            
            // add a tree sorter in folder mode
            // new Tree.TreeSorter(tree2, {folderSort:true});
            
            // add the root node
            var root2 = new Tree.AsyncTreeNode({
                text: 'Congress', 
                draggable:false, 
                id:'ux'
            });
            
            tree2.setRootNode(root2);
            
            tree2.render('congress');
            
            root2.expand(true);
        }
    };
}();

Ext.EventManager.onDocumentReady(TreeTest.init, TreeTest, true);