<?php

/**
 * Class: dbInterface
 * Purpose: To allow common database queries to be answered easily
 * and to decrease the number of database calls necessary
 * elsewhere in code.
 */
include 'database.php';
class dbInterface{
    private $db;    //the database object to perform queries on

    //constructor that instantiates a database object
    function __construct(){
        ob_start();
        $this->db = new database();
        ob_end_clean();
    }

    /**
     * @return array    an associative array containing basic data for all recipes where each index is a single entry
	 * Data Currently Output:
     * array of recipe ids in $output["ids"]
     * array of recipe titles in $output["titles"]
     * integer that is the number of recipe entries in $output["count"]
     * array of recipe pictures in $output["pictures"]
     */
    function getRecipeList(){
        $output = array();
        $output["ids"] = $this->getIDs();   //add ids to output array
        $output["titles"] = $this->getTitles(); //add titles to output array
        $output["pictures"] = $this->getPictures(); //add the recipe thumbnail pictures
        $output["count"] = $this->getCount();   //add the number of entries in the recipes table
        return $output;
    }

    /**
     * Gathers basic information for a set number of recipes (currently 9) given a location to start from
     * @param $page the page of recipes requested
     * @return array   an associative array containing basic data for all recipes where each index is a single entry
     *                      array of recipe ids in $output["ids"]
     *                      array of recipe titles in $output["titles"]
     *                      array of recipe pictures in $output["pictures"]
     *                      integer that is the number of entries in the recipes table in $output["count"]
     */
    function getRecipeListN($page){
        define("ENTRIES", 9);
        $start = (($page - 1) * ENTRIES);
        $output = array();
        $output["ids"] = $this->getIDsN($start, ENTRIES);
        $output["titles"] = $this->getTitlesN($start, ENTRIES);
        $output["pictures"] = $this->getPicturesN($start, ENTRIES);
        $output["count"] = $this->getCount();
        return $output;
    }

    /**
     * @return array    array of recipe titles
     */
    function getTitles(){
        $query = "SELECT * FROM recipes ORDER BY id;";
        $relevant = array("title");
        $result = $this->db->sendCommandParse($query,$relevant);    //retrieve titles
        return $result;
    }

    /**
     * @param $id   the id of the recipe requested
     * @return array    associative array containing all relevant data for a single recipe entry
     * Data Currently Output:
     * title string in $output["title"]
     * array of ingredient strings in $output["ingredients"]
     * array of instruction strings in $output["instructions"]
     * string that contains the picture path in $output["picture"]
     */
    function getRecipe($id){
        $output = array();
        $output["title"] = $this->getTitle($id);
        //check if id is a valid recipe id
        if ($output["title"] == null){
            $output["error"] = -1;  //error code
            return $output;
        }else{
            $output["error"] = 0;   //no error
        }
        $output["ingredients"] = $this->getIngredients($id);
        $output["instructions"] = $this->getInstructions($id);
        $output["picture"] = $this->getPicture($id);
        $output["author"] = $this->getAuthor($id);
        return $output;
    }

    /**
     * @param $data     an associative array that contains information in the following format
     * data["title"]:                  the name of the recipe as a string
     * data["parent_id"]:              the id of the parent recipe as an integer | NUll if no parent
     * data["ingredient_measurement"]: an array of ingredient measurement strings in the order they appear
     * data["ingredient_name"]:        an array of ingredient name strings in the order they appear
     * data["instructions"]:           an array of instruction text strings in the order they appear
     * data["picture"]:                a flag for whether a picture was uploaded
     *
     * @return int     ID of the newly added recipe in the recipes table
     *
     * Inserts a new recipe and its associated instructions and ingredients
     * Note:  this function assumes that a correctly formatted directory will be built for the picture file
     */
    function insertRecipe($data){
        if (gettype($data["parent_id"]) !== "integer" && $data["parent_id"] != "NULL" && $data["parent_id"] !== NULL){
            die("Invalid parent Id passed in data['parent_id']. This should be an integer.");
        }
        if (gettype($data["title"]) !== "string"){
            die("Invalid title passed in data['title']. This should be a string.");
        }
        if (gettype($data["ingredient_measurement"]) !== "array" || gettype($data["ingredient_measurement"][0]) != "string"){
            die("Invalid measurement passed in data['ingredient_measurement']. This should be an array of strings.");
        }
        if (gettype($data["ingredient_name"]) !== "array" || gettype($data["ingredient_name"][0]) !== "string"){
            die("Invalid name passed in data['ingredient_name']. This should be an array of strings.");
        }
        if (gettype($data["instructions"]) !== "array" || gettype($data["instructions"][0]) !== "string"){
            die("Invalid instruction passed in data['instructions']. This should be an array of strings.");
        }
        if (gettype($data["author"]) !== "integer" || $data["author"] <= 0){
            die("Invalid author passed in data['author']. This should be a positive integer, or 0 for anonymous.");
        }
        $id = -1;
       
        //insert main recipe entry
        if ($data["parent_id"] === NULL){
            $data["parent_id"] = "NULL";
        }
        $query = "INSERT INTO recipes (parent_id, title, picture, author) VALUES (" . $data["parent_id"] . ", '" .
            $data["title"] . "', '/imageError.png', " . $data["author"] .")";
        $this->db->sendCommand($query);
        $id = $this->db->getLastID();   //find the id of the newly inserted recipe

        if ($data["picture"]) {
            //insert the assumed path to the picture file
            $directory = "/rec" . $id . "/rec" . $id . "_0.jpg";
            $query = "UPDATE recipes SET picture='" . $directory . "' WHERE id=" . $id;
            $this->db->sendCommand($query);
        }
        //insert ingredients to ingredients table
        for ($i = 0; $i < count($data["ingredient_measurement"]); $i++){
            $query = "INSERT INTO ingredients (rec_id, order_num, measurement, name) VALUES (" .
                $id . ", " . ($i+1) . ", '" . $data["ingredient_measurement"][$i] . "', '" . $data["ingredient_name"][$i] . "')";
            $this->db->sendCommand($query);
        }

        //insert instructions to instructions table
        for ($i = 0; $i < count($data["instructions"]); $i++){
            $query = "INSERT INTO instructions (rec_id, order_num, instruction_text) VALUES (" . $id . ", " .
                ($i+1) . ", '" . $data["instructions"][$i] . "')";
            $this->db->sendCommand($query);
        }
        return $id; //the id of the new entry in the recipes table
    }

    /**
     * @return array    array of recipe ids
     */
    function getIDs(){
        $query = "SELECT * FROM recipes ORDER BY id";
        $relevant = array("id");
        $result = $this->db->sendCommandParse($query, $relevant);   //retrieve id's
        return $result;
    }

    /**
     * Gets the current number of entries in the recipes table
     * @return mixed    the number of entries in the recipes table as an integer
     */
    private function getCount(){
        $query = "SELECT COUNT(*) FROM recipes";
        $relevant = array("COUNT(*)");
        $result = $this->db->sendCommandParse($query, $relevant);
        return $result[0];
    }

    /**
     * @param $start    the entry number the current page starts at
     * @param $entries  the number of entries in a page
     * @return array    array of <$entries> ids starting at the <$start> entry
     */
    private function getIDsN($start, $entries){
        $query = "SELECT * FROM recipes ORDER BY id LIMIT " . $start . ", " . $entries;
        $relevant = array("id");
        $result = $this->db->sendCommandParse($query, $relevant);
        return $result;
    }
	
    /**
     * @return array    array of recipe thumbnail pictures
     */
    private function getPictures(){
        $query = "SELECT * FROM recipes ORDER BY id";
        $relevant = array("picture");
        $result = $this->db->sendCommandParse($query, $relevant);
        return $result;
    }

    /**
     * @param $start    the entry number the current page starts at
     * @param $entries  the number of entries in a page
     * @return array    array of <$entries> picture paths starting at the <$start> entry
     */
    private function getPicturesN($start, $entries){
        $query = "SELECT * FROM recipes ORDER BY id LIMIT " . $start . ", " . $entries;
        $relevant = array("picture");
        $result = $this->db->sendCommandParse($query, $relevant);
        return $result;
    }

    /**
     * @param $id   the id of the recipe requested
     * @return mixed    the title of that recipe
     */
    public function getTitle($id){
        $query = "SELECT * FROM recipes WHERE id=" . $id;
        $relevant = array("title");
        $result = $this->db->sendCommandParse($query, $relevant); //retrieve title
        return $result[0];
    }

    /**
     * @param $start    the entry number the current page starts at
     * @param $entries  the number of entries in a page
     * @return array    array of <$entries> titles starting at the <$start> entry
     */
    private function getTitlesN($start, $entries){
        $query = "SELECT * FROM recipes ORDER BY id LIMIT " . $start . ", " . $entries;
        $relevant = array("title");
        $result = $this->db->sendCommandParse($query, $relevant);
        return $result;
    }

    /**
     * @param $id   the id of the requested recipe
     * @return array    array containing the ingredients associated with that recipe
     */
    private function getIngredients($id){
        $query = "SELECT * FROM ingredients WHERE rec_id=" . $id . " ORDER BY order_num";
        $relevant = array("measurement", "name");
        $result = $this->db->sendCommandParse($query, $relevant);
        $output = array(); //array for output
        //create array of ingredients
        for ($i = 0; $i < count($result); $i += 2){
            $output[$i / 2] = $result[$i] . " " . $result[$i + 1];
        }
        return $output;
    }

    /**
     * @return array array containing all ingredients
     */
    public function getAllIngredients() {
	$query    = "SELECT rec_id, name FROM ingredients";
	$relevant = array("rec_id", "name");
	$result   = $this->db->sendCommandParse($query, $relevant);
	
	return $result;
    }

    /**
     * @param $id   the id of the requested recipe
     * @return array    array containing the instructions associated with that recipe
     */
    private function getInstructions($id){
        $query = "SELECT * FROM instructions WHERE rec_id=" . $id . " ORDER BY order_num";
        $relevant = array("instruction_text");
        $result = $this->db->sendCommandParse($query, $relevant);
        return $result;
    }

    /**
     * @param $id   the id of the requested recipe
     * @return mixed    string containing the picture address
     */
    private function getPicture($id){
        $query = "SELECT * FROM recipes WHERE id=" . $id;
        $relevant = array("picture");
        $result = $this->db->sendCommandParse($query, $relevant);
        return $result[0];
    }

	/**Search our database for an ingredient that starts with $text
	* Return an array of results that match this pattern
	*/
	function getIngredientAutocomplete($text) {

		$query = "SELECT * FROM ingredients WHERE name LIKE '$text%' ORDER BY name ASC LIMIT 10";

		$matches = array();
		if ($text == "") { return $matches; }

		$result = $this->db->sendCommand($query);
		while ($row = mysqli_fetch_array($result)) {
			$matches[] = $row['name'];
		}
        return $matches;
    }

    private function getAuthor($id){
        $query = "SELECT author FROM recipes where id =" . $id . ";";
        $relevant = array("author");
        $result = $this->db->sendCommandParse($query, $relevant);
        $userId = $result[0];
        if($userId == null)
        {
            return null;
        }
        $query = "SELECT name FROM users where id =" . $userId . ";";
        $relevant = array("name");
        $result = $this->db->sendCommandParse($query, $relevant);
        return $result;
  }

    function __destruct(){
        ob_start();
        $this->db = null;
        ob_end_clean();
    }
}


