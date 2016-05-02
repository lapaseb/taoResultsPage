<?php
/**  
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 * 
 * Copyright (c) 2016 (original work) Franc Romain;
 *               2016 (update and modification)  Open Assessment Technologies SA;               
 * 
 */               

return array(
    'name' => 'taoResultsPage',
	'label' => 'Results Page',
	'description' => 'An alternate resulst visualisation',
    'license' => 'GPL-2.0',
    'version' => '1.0',
	'author' => 'Franc Romain',
	'requires' => array(
        'taoOutcomeUi' => '>=2.7.4'
    ),
	'managementRole' => 'http://www.tao.lu/Ontologies/generis.rdf#taoResultsPageManager',
    'acl' => array(
        array('grant', 'http://www.tao.lu/Ontologies/generis.rdf#taoResultsPageManager', array('ext'=>'taoResultsPage')),
    ),
    'uninstall' => array(
    ),
    'routes' => array(
        '/taoResultsPage' => 'Zeldroxe\\taoResultsPage\\controller'
    ),    
	'constants' => array(
	    # views directory
	    "DIR_VIEWS" => dirname(__FILE__).DIRECTORY_SEPARATOR."views".DIRECTORY_SEPARATOR,
	    
		#BASE URL (usually the domain root)
		'BASE_URL' => ROOT_URL.'taoResultsPage/',
	    
	    #BASE WWW required by JS
	    'BASE_WWW' => ROOT_URL.'taoResultsPage/views/'
	),
    'extra' => array(
        'structures' => dirname(__FILE__).DIRECTORY_SEPARATOR.'controller'.DIRECTORY_SEPARATOR.'structures.xml',
    )
);