<?php
/**
 * Class: Database
 * Purpose : An Interface with the MySQL Database
 * The class is to be instantatied when data is to 
 * be input and destructed when the information has been input
 */
class database {
	/* Private Data Members */
	private $conn;
	
	/**
	 * Instaintates an new connection to the database
	 */
	function __construct() {
		$server   = "141.219.196.115";
		$username =             "tsp";
		$password =            "null";
		$db       =           "tspdb";
		// Create a Connection
		$this->conn  = new mysqli($server, $username, $password, $db);

		// Verify a Connection was made
		if ($this->conn->connect_error) {
			die("Connection failed: " . $this->conn->connect_error);
		} else {
			echo ("Connected Successfully\n");
		}
	}
	
	/**
	 * Send a command to the SQL server and return the result
	 * Input : $cmd SQL Command as a string         (i.e "SELECT * FROM recipes;")
	 * Output: result of query
	 */
	function sendCommand($cmd) {
		$result = mysqli_query($this->conn, $cmd);
		return $result;
	}
	
	/**
	 * Send a command to the SQL server and returns and the parsed return value
	 * Input : $cmd SQL Command as a string         (i.e "SELECT * FROM recipes;")
	 *         $array List of requested information (i.e ["recipes", "id", "parent_id"])
	 * Output: an array of the result of query intersect with the requested data
	 */
	function sendCommandParse($cmd, $array) {
		$result = mysqli_query($this->conn, $cmd);
		$size = count($array);
		$r = array();
		
		/* store the data requested in an array */
		while($row = $result->fetch_assoc()) {
				$m = 0;
				while($m < $size) {
					array_push($r, $row[$array[$m]]);
					$m = $m + 1;
				}
		}
		return $r;
	}
	
	/**
	 * Closes the Connection to the database.
	 */
	function __destruct() {
		echo "Closing Connection to Database...";
		$this->conn->close();
		echo "Done\n";
	}
}
