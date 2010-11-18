<?php
/**************************************************************************\
* Protean Framework                                                        *
* https://github.com/erictj/protean                                        *
* Copyright (c) 2006-2010, Loopshot Inc.  All rights reserved.             *
* ------------------------------------------------------------------------ *
*  This program is free software; you can redistribute it and/or modify it *
*  under the terms of the BSD License as described in license.txt.         *
\**************************************************************************/

interface PFCache { 

	static public function getInstance();
	static public function init();
	static public function fetch($key); 
	static public function store($key, &$data, $ttl=null);
	static public function delete($key);
}

?>