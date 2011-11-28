/****************************************************************************
 * todoyu is published under the BSD License:
 * http://www.opensource.org/licenses/bsd-license.php
 *
 * Copyright (c) 2011, snowflake productions GmbH, Switzerland
 * All rights reserved.
 *
 * This script is part of the todoyu project.
 * The todoyu project is free software; you can redistribute it and/or modify
 * it under the terms of the BSD License.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the BSD License
 * for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script.
 *****************************************************************************/

/**
 * Sortable node (task)
 *
 * @module		Project
 * @namespace	Todoyu.Ext.project.TaskTree
 * @class		SortableNode
 * @see			Todoyu.Ext.project.TaskTree.Sortable
 */
Todoyu.Ext.project.TaskTree.SortableNode = Class.create({
	/**
	 * Tree
	 * @var	{Todoyu.Ext.project.TaskTree.Sortable}
	 */
	tree: null,

	/**
	 * Parent node
	 * @var	{Todoyu.Ext.project.TaskTree.SortableNode}
	 */
	parent: null,

	/**
	 * HTML element
	 * @var	{Element}
	 */
	element: null,

	/**
	 * Draggable object
	 * @var	{Draggable}
	 */
	drag: null,

	/**
	 * Drop zone element
	 * @var	{Element}
	 */
	drop: null,

	/**
	 * Options
	 * @var	{Object}
	 */
	options: {},
	
	/**
	 * Extension back ref
	 * @var	{Object}
	 */
	ext: Todoyu.Ext.project,

	/**
	 * Top offset of node
	 * @var	{Number}
	 */
	topOffset: 0,

	

	/**
	 * Initialize node
	 *
	 * @param	{Todoyu.Ext.project.TaskTree.Sortable}		tree
	 * @param	{Todoyu.Ext.project.TaskTree.SortableNode}	parent
	 * @param	{Element}									element
	 * @param	{Object}									options
	 */
	initialize: function(tree, parent, element, options) {
		this.tree		= tree;
		this.parent 	= parent;
		this.element	= $(element);
		this.options	= options;
		this.children	= [];

		this.droppableOptions = Object.extend({
			onHover: 	this.onHover.bind(this),
			onDrop:		this.onDrop.bind(this),
			overlap:	'vertical',
			accept:		'task'
		}, options.droppable);

		this.draggableOptions = Object.extend({
			revert: 	'failure',
			constraint: 'vertical',
			handle: 	'dndHandle',
			onStart: 	this.onDragStart.bind(this),
			onEnd: 		this.onDragEnd.bind(this),
			scroll:		window,
			snap:		this.snapDraggable.bind(this)
		}, options.draggable);

		this.initChildren();
	},



	/**
	 * Initialize child nodes
	 */
	initChildren: function() {
		var childNodes = [];

			// Look for sub tasks/child nodes
		if( this.isRootNode() ) {
			childNodes = this.element.childElements();
		} else {
			var subtaskContainer = this.element.down('div.subtasks');
			if( subtaskContainer ) {
				childNodes = subtaskContainer.childElements();
			}
		}

		childNodes.each(function(task){
			this.addChild(new Todoyu.Ext.project.TaskTree.SortableNode(this.tree, this, task, this.options));
		}, this);
	},



	/**
	 * Remove a child from this node
	 *
	 * @param	{Todoyu.Ext.project.TaskTree.SortableNode}	child
	 */
	removeChild: function(child) {
		 this.children.splice(this.children.indexOf(child), 1);
	},



	/**
	 * Add a child to this node
	 *
	 * @param	{Todoyu.Ext.project.TaskTree.SortableNode}	child
	 */
	addChild: function(child) {
		this.children.push(child);
		child.parent = this;
	},



	/**
	 * Log message
	 *
	 * @param	{String}	message
	 * @param	{Object}	item
	 */
	log: function(message, item) {
		this.tree.log(message, item);
	},



	/**
	 * Find a node (for the element) in this node or in one of the sub nodes (recursive)
	 *
	 * @param	{Element}	element
	 */
	findNode: function(element) {
		if( element == this.element) {
			return this;
		}

		for(var i = 0; i < this.children.length; i++) {
			var node = this.children[i].findNode(element);
			if(node) {
				return node;
			}
		}

		return false;
	},



	/**
	 * Destroy node
	 */
	destroy: function() {
		this.disableSortable(false);
		this.children.each(function(child, index){
			child.destroy();
			delete this.children[index];
		}, this);
		this.children = [];
	},



	/**
	 * Make node sortable
	 * Scriptaculous says to make the children droppable first
	 */
	makeSortable: function() {
			// Make all children sortable
		this.children.each(function(child){
			child.makeSortable();
		});

		if( !this.drop ) {
				// Create a drop zone on the node
			this.enableDrop();
				// Make draggable if not the root node
			this.enableDrag();
		}
	},

	

	/**
	 * Disable sorting
	 * 
	 * @param recursive
	 */
	disableSortable: function(recursive) {
		this.log('Disable sorting: ' + this.element.id);
		this.disableDrag();
		this.disableDrop();

		if( recursive !== false ) {
			this.children.each(function(child){
				child.disableSortable();
			});
		}
	},



	/**
	 * Enable dragging for this node
	 */
	enableDrag: function() {
		if( !this.drag && !this.isRootNode() ) {
			this.log('Enable dragging: ' + this.element.id);
			this.drag = new Draggable(this.element, this.draggableOptions);
		}
	},



	/**
	 * Disable dragging for this node
	 */
	disableDrag: function() {
		if( this.drag ) {
			this.log('Disable dragging: ' + this.element.id);
			this.drag.destroy();
			this.drag = null;
		}
	},



	/**
	 * Create a drop zone for this node
	 */
	enableDrop: function() {
		if( !this.drop && !this.isRootNode() ) {
			var dropZone = this.getDropZone();
			if( dropZone ) {
				this.log('Enable dropping: ' + this.element.id);
				this.drop = dropZone;
				Droppables.add(dropZone, this.droppableOptions);
			}
		}
	},



	/**
	 * Remove the drop zone for this node
	 */
	disableDrop: function() {
		if( this.drop ) {
			this.log('Disable dropping: ' + this.element.id);
			Droppables.remove(this.drop);
			this.drop = null;
		}
	},



	/**
	 * Handler when drag started
	 *
	 * @param	{Draggable}		draggable
	 * @param	{Event}			event
	 */
	onDragStart: function(draggable, event) {
		this.disableDrop();

			// Get top offset
		this.topOffset = this.element.cumulativeOffset().top;

		if( !this.isRootTask() ) {
			draggable.oldParentID = this.getParentTaskID();
		}

		this.log('Start dragging');
	},



	/**
	 * Handler when drag ended
	 *
	 * @param	{Draggable}	draggable
	 * @param	{Event}		event
	 */
	onDragEnd: function(draggable, event) {
		this.enableDrop();
		this.hideMarker();

		if( draggable.oldParentID ) {
			this.ext.Task.updateSubTasksExpandTrigger(draggable.oldParentID);
			delete draggable.oldParentID;
		}

		this.log('Stop dragging');
	},



	/**
	 * Check whether draggable is still in the task tree
	 * Hide marker if not
	 * Note: Reducing the offset doesn't help, because the overlapping depends on the mouse and not the element
	 *
	 * @param	{Number}	x
	 * @param	{Number}	y
	 * @return	{Array}
	 */
	snapDraggable: function(x, y) {
			// Half task height as buffer
		var buffer = 11;

			// Dragged upwards
		if( y < 0 ) {
			var offsetDiff	= this.topOffset - this.tree.topOffset;
			if( -y > offsetDiff + buffer ) {
				this.hideMarker();
//				this.getMarker().addClassName('outside');
			}
		}

			// Dragged downwards
		if( y > 0 ) {
			var lostTasks	= this.tree.element.down('div.lostTasks');
			var lostHeight	= lostTasks ? lostTasks.getHeight()+20 : 0;
			var treeHeight	= this.tree.element.getHeight() - lostHeight;
			var dragTop		= this.topOffset + y;
			var treeBottom	= this.tree.topOffset + treeHeight;

			if( dragTop + buffer > treeBottom ) {
//				this.getMarker().addClassName('outside');
				this.hideMarker();
			}
		}

		return [x,y];
	},



	/**
	 * Get drop zone of the element
	 *
	 * @return	{Element}
	 */
	getDropZone: function() {
		return this.element.down('h3');
	},



	/**
	 * Check whether current node is the root node
	 *
	 * @return	{Boolean}
	 */
	isRootNode: function() {
		return this.parent === null;
	},



	/**
	 * Check whether node is a root task (first level task)
	 *
	 * @return	{Boolean}
	 */
	isRootTask: function() {
		return this.parent.isRootNode();
	},



	/**
	 * Handler on task hover
	 *
	 * @param	{Element}	drag
	 * @param	{Element}	drop
	 * @param	{Number}	overlap
	 */
	onHover: function(drag, drop, overlap) {
		this.dropPosition = overlap < 0.33 ? 'after' : overlap > 0.77 ? 'before' : 'in';

			// Prevent child dropping
		if( !this.isChild(drop, drag) ) {
			this.mark(this.dropPosition);
		}
	},



	/**
	 * Check whether element a
	 *
	 * @param	{Element}	elementA
	 * @param	{Element}	elementB
	 * @return	{Boolean}
	 */
	isChild: function(elementA, elementB) {
		return elementA.up('#' + elementB.id) !== undefined;
	},



	/**
	 * Handler on task drop
	 *
	 * @param	{Element}	drag
	 * @param	{Element}	drop
	 * @param	{Event}		event
	 */
	onDrop: function(drag, drop, event) {
			// Prevent child dropping
		if( !this.isChild(drop, drag) ) {
			this.hideMarker();
			this.insertTask(this.dropPosition, drag, event);

			this.log('Dropped item: ' + drag.id);

			if( this.options.onDrop ) {
				 this.options.onDrop(drag, drop, event);
			}
		}
	},



	/**
	 * Insert a task
	 * @param	{String}	position		in, after, before
	 * @param	{Element}	drag
	 * @param	{Event}		event
	 */
	insertTask: function(position, drag, event) {
		this.removeDraggingStyles(drag);

		var idTaskDrag	= drag.id.split('-')[1];
		var idTaskDrop	= this.getTaskID();
		var dragNode	= this.tree.findNode(drag);

		var newParent	= position === 'in' ? this : this.parent;
		dragNode.setNewParent(newParent);

			// Insert element at new position depending of drop position
		switch( position ) {
			case 'in':
				this.insertTaskAsSubtask(drag);
				break;

			case 'after':
				this.insertTaskAfter(drag);
				this.tree.onChange(idTaskDrag, idTaskDrop, position);
				break;

			case 'before':
				this.insertTaskBefore(drag);
				this.tree.onChange(idTaskDrag, idTaskDrop, position);
				break;
		}
	},



	/**
	 * Set new parent node
	 *
	 * @param	{Todoyu.Ext.project.TaskTree.SortableNode}	newParent
	 */
	setNewParent: function(newParent) {
		if( this.parent !== newParent ) {
			this.parent.removeChild(this);
			newParent.addChild(this);
		}
	},



	/**
	 * Remove some styles left over by the library
	 *
	 * @param	{Element}	drag
	 */
	removeDraggingStyles: function(drag) {
		drag.setStyle({
			top:	0
		});
	},



	/**
	 * Get current task ID (node is a drop zone)
	 *
	 * @return	{String}
	 */
	getTaskID: function() {
		return this.element.id.split('-')[1];
	},



	/**
	 * Get task element (div)
	 *
	 * @return	{Element}
	 */
	getTask: function() {
		return this.element;
	},



	/**
	 * Get the id of the parent task
	 *
	 * @return	{Number|Boolean}
	 */
	getParentTaskID: function() {
		var parentTask = this.getParentTask();

		if( parentTask ) {
			return parentTask.id.split('-').last();
		} else {
			return false;
		}
	},



	/**
	 * Get the parent task
	 *
	 * @return	{Element|undefined}
	 */
	getParentTask: function() {
		return this.element.up('.task');
	},



	/**
	 * Insert task as sub task of current task node
	 *
	 * @param	{Element}	drag
	 */
	insertTaskAsSubtask: function(drag) {
		var idTaskDrop	= this.getTaskID();
		var idTaskDrag	= drag.id.split('-')[1];

		if( this.ext.TaskTree.areSubTasksLoaded(idTaskDrop) ) {
			this.ext.TaskTree.expandSubTasks(idTaskDrop);
			this.ext.Task.getSubTasksContainer(idTaskDrop).insert(drag);
			this.ext.Task.updateSubTasksExpandTrigger(idTaskDrop);
			this.tree.onChange(idTaskDrag, idTaskDrop, 'in');
		} else {
			drag.remove();
			this.ext.TaskTree.loadSubTasks(idTaskDrop, function(idTask){
				this.ext.TaskTree.setSubtaskTriggerExpanded(idTask, true);
				this.ext.Task.getSubTasksContainer(idTaskDrop).insert(drag);
				drag.highlight();
				this.ext.Task.updateSubTasksExpandTrigger(idTaskDrop);
				this.tree.onChange(idTaskDrag, idTask, 'in');
				this.tree.reload();
			}.bind(this));
		}
	},



	/**
	 * Insert task after current task node
	 *
	 * @param	{Element}	drag
	 */
	insertTaskAfter: function(drag) {
		this.getTask().insert({
			after: drag
		});
	},



	/**
	 * Insert task before current task node
	 *
	 * @param	{Element}	drag
	 */
	insertTaskBefore: function(drag) {
		this.getTask().insert({
			before: drag
		});
	},



	/**
	 * Mark current node as drop zone for current postion
	 *
	 * @param position
	 */
	mark: function(position) {
		this.hideMarker();

		switch(position) {
			case 'in':
			case 'before':
				this.element.insert({
					before: this.getMarker()
				});
				break;

			case 'after':
				this.element.insert({
					after: this.getMarker()
				});
				break;
		}

		this.getMarker().addClassName(position);
		this.showMarker();
	},



	/**
	 * Mark current element as target
	 */
	markAsTarget: function() {
		this.element.addClassName('dragDropTarget');
	},

	

	/**
	 * Get marker element
	 *
	 * @return	{Element}
	 */
	getMarker: function() {
		return this.tree.getMarker();
	},



	/**
	 * Show marker
	 */
	showMarker: function() {
		this.tree.showMarker();
		this.markAsTarget();
	},



	/**
	 * Hide marker
	 *
	 * Also remove inside class which may be added
	 * and move the marker back to the body element to prevent any removed by task refresh action
	 */
	hideMarker: function() {
		this.tree.hideMarker();
		this.tree.unmarkActiveTask();
	},



	/**
	 * Get debug tree for this node
	 *
	 * @return	{Object}
	 */
	getDebugTree: function() {
		var data = {
			parent: this.parent,
			task: this.isRootNode() ? null : this.getTaskID(),
			element: this.element,
			children: []
		};

		if( this.children.length > 0 ) {
			this.children.each(function(child){
				data.children.push(child.getDebugTree());
			}, this);
		}

		return data;
	}
});