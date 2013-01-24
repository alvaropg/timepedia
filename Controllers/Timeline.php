<?php

require 'sparqllib.php';

define("MAX_DATES", 50);

class Controllers_Timeline extends RestController
{
	private $db;
	private $articles_count;

	public function __construct($request)
	{
		$this->request = $request;

		$this->db = sparql_connect("http://dbpedia.org/sparql/");
		if (!$this->db) {
			print sparql_errno() . ": " . sparql_error(). "\n";
			exit;
		}

		$this->articles_count = 0;
	}

	public function get()
	{
		$parms = $this->request['params'];

		if (!array_key_exists('category', $parms)) {
			throw new Exception('Bad request. No category', 400);
		}
		$data = $this->populateTimeline($parms['category']);

		$this->response = $data;
		$this->responseStatus = 200;
	}

	public function post()
	{
		$this->response = array('TestResponse' => 'I am POST response. Variables sent are - ' . http_build_query($this->request['params']));
		$this->responseStatus = 201;
	}

	public function put()
	{
		$this->response = array('TestResponse' => 'I am PUT response. Variables sent are - ' . http_build_query($this->request['params']));
		$this->responseStatus = 200;
	}

	public function delete()
	{
		$this->response = array('TestResponse' => 'I am DELETE response. Variables sent are - ' . http_build_query($this->request['params']));
		$this->responseStatus = 200;
	}

	private function populateMainArticle ($article)
	{
		// Populate
		$query = "SELECT DISTINCT ?label ?comment ?image ?date WHERE {
					<http://dbpedia.org/resource/$article> rdfs:label ?label .
					<http://dbpedia.org/resource/$article> rdfs:comment ?comment .
					OPTIONAL {
      					<http://dbpedia.org/resource/$article> <http://dbpedia.org/property/date> ?date .
      					<http://dbpedia.org/resource/$article> <http://dbpedia.org/ontology/thumbnail> ?image .
   					}
   					FILTER (LANG(?label) = 'en' AND LANG(?comment) = 'en')
				  } LIMIT 1";
		$result = sparql_query($query);
		if( !$result ) {
			print sparql_errno() . ": " . sparql_error(). "\n"; exit;
		}
		$row = sparql_fetch_array($result);
		return array ('label' => $row['label'],
					  'comment' => $row['comment'],
					  'image' => $row['image'],
				      'date'  => $row['date']);
	}

	private function _get_data_from_url ($url)
	{
		$ch = curl_init();
		$timeout = 5;
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		curl_setopt($ch, CURLOPT_USERAGENT, "booyah!");
		$data = curl_exec($ch);
		curl_close($ch);
		return $data;
	}

	private function getMainArticle ($wikiurl)
	{
		$content = $this->_get_data_from_url($wikiurl);
		preg_match('#<div class="rellink relarticle mainarticle">The main article for this <a href="/wiki/Help:Categories" title="Help:Categories">category</a> is <b><a href="/wiki/(.*)" title="#', $content, $match);
		return $match[1];	
	}

	private function populateArticle ($article)
	{
		
	}

	private function populateCategory ($category, $populate = FALSE)
	{
		$articles = array();
		$articles_count = 0;
		// 1. Get the x first articles in the category with date
		$query = "select distinct ?s ?label ?comment ?image ?date_type ?date where {
			 		 ?s <http://purl.org/dc/terms/subject> <http://dbpedia.org/resource/Category:$category> .
			 		 ?s rdfs:label ?label .
					 ?s rdfs:comment ?comment .
			 		 ?s ?date_type ?date .
			 		 OPTIONAL {?s <http://dbpedia.org/ontology/thumbnail> ?image}
			 		 FILTER (datatype(?date) = xsd:date AND LANG(?label)='en' AND LANG(?comment)='en')
				  } LIMIT " . (MAX_DATES * 2);
		$result = sparql_query($query);
		if (!$result) {
			print sparql_errno() . ": " . sparql_error(). "\n";
			exit;
		}
		while (($row = sparql_fetch_array($result)) AND $articles_count < MAX_DATES) {
			$articles[] = array ('label' => $row['label'],
								 'comment' => $row['comment'],
								 'date' => $row['date'],
								 'date_type' => $row['date_type'],
			                     'image' => $row['image']);
			$articles_count++;
		}
		// 2. Gets the category in the category and make a recursive search until completed articles
		if ($articles_count < MAX_DATES AND $populate) {
			$query = "SELECT DISTINCT ?category {
						?category skos:broader category:$category
					  } LIMIT 10";
		}

		return $articles;
	}

	private function populateTimeline ($category)
	{
		// Category info
		$query = "select ?label ?wikiurl where {
		              <http://dbpedia.org/resource/Category:$category> <http://www.w3.org/2000/01/rdf-schema#label> ?label .
		              <http://dbpedia.org/resource/Category:$category> <http://www.w3.org/ns/prov#wasDerivedFrom> ?wikiurl .
	              } LIMIT 1";
		$result = sparql_query($query);
		if (!$result) {
			print sparql_errno() . ": " . sparql_error(). "\n";
			exit;
		}
		$row = sparql_fetch_array($result);
		$data = array ('Timeline' => $row['label']);
		// Main article
		$main_article = $this->getMainArticle ($row['wikiurl']);
		$data['MainArticle'] = $this->populateMainArticle($main_article);
		// Articles in this category
		$data['Events'] = $this->populateCategory($category);

		return $data;
	}

}
