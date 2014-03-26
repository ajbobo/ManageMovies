
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<title>Manage Storage Locations</title>
	<link rel="stylesheet" type="text/css" href="ManageMovies.css">
<?php
function test_input($data)
{
	$data = trim($data);
	$data = stripslashes($data);
	$data = htmlspecialchars($data);
	return $data;
} 
?>
</head>
<body>
<h1 class="center">Manage Storage Locations</h1>
<?php
	$con = mysqli_connect("localhost", "AJB", "ajb", "movies");
	if (mysqli_connect_errno())
	{
		echo '<span class="error">Unable to connect to database: ' . mysqli_connect_error() . "</span>\n";
	}
	
	// Handle POST messages when the Submit button was pressed
	$nameerr = $typeerr = "";
	$name = $type = "";
	if ($_SERVER["REQUEST_METHOD"] == "POST")
	{
		// Error checking/reporting
		if (empty($_POST["name"]))
			$nameerr = "A name is required";
		else
			$name = test_input($_POST["name"]);
		
		if (empty($_POST["type"]))
			$typeerr = "A type is required";
		else
			$type = test_input($_POST["type"]);
		
		// Got valid data - do something with it
		if (strlen($nameerr) == 0 && strlen($typeerr) == 0)
		{
			// Put the new container in the database
			$result = mysqli_query($con, "INSERT INTO containers VALUES ('$name', '$type');");
			if (mysqli_errno($con))
			{
				echo "<p class='error'>" . mysqli_error($con) . "</p>\n";
			}
			else
			{
				// Figure out how many locations are in the new container, and make entries for them
				$results = mysqli_query($con, "SELECT Sections, Slots FROM container_types WHERE Name = '$type'");
				$row = mysqli_fetch_array($results);
				$sections = $row[0];
				$slots = $row[1];
				for ($section = 1; $section <= $sections; $section++)
				{
					for ($slot = 1; $slot <= $slots; $slot++)
					{
						mysqli_query($con, "INSERT INTO locations (Container, Section, Slot) VALUES ('$name','$section','$slot')");
					}
				}
			}
			mysqli_free_result($result);
		}
	}
?>
<hr>
<h2>Currently available Containers:</h2>
<?php	
	$result = mysqli_query($con,
		"SELECT containers.Name, containers.Type, COUNT(*) as 'Total Slots', COUNT(*) - Count(DISTINCT Disk) as 'Empty Slots' 
		FROM locations, containers 
		WHERE locations.Container = containers.Name
		GROUP BY containers.Name");
	$fields = mysqli_fetch_fields($result);
	$numfields = count($fields);
	echo '<table border="1">';
	foreach ($fields as $field)
		echo "<th>" . $field->name . "</th>";
	echo "\n";
	while ($row = mysqli_fetch_array($result, MYSQLI_NUM))
	{
		echo "\t<tr>";
		for ($x = 0; $x < $numfields; $x++)
			echo '<td>' . $row[$x] . '</td>';
		echo "</tr>\n";
	}
	echo "</table>";
	mysqli_free_result($result);
?>
<hr>
<h2>Add New Container</h2>
<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
Name: <input type="text" name="name"><span class="error"><?php echo $nameerr; ?></span>
Type: <select name="type">
<?php
	$result = mysqli_query($con, "SELECT Name FROM container_types");
	while ($row = mysqli_fetch_array($result))
		echo '<option value="' . $row[0]. '">' . $row[0] . "</option>\n"; 
	mysqli_free_result($result);
?>
</select><span class="error"><?php echo $typeerr; ?></span>
<input type="submit" value="Submit">
</form>
<?php
	mysqli_close($con);
?>
</body>
</html>