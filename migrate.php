<?php
require_once 'db.php';

// Read JSON data
$jsonData = file_get_contents(__DIR__ . '/data/health_data.json');
$data = json_decode($jsonData, true);

if (!$data) {
    die("Error decoding JSON data");
}

// Drop tables if they exist (Reverse order of dependencies)
$tables = ['local_fruits', 'meals', 'countries', 'continents', 'jobs'];
foreach ($tables as $table) {
    mysqli_query($conn, "DROP TABLE IF EXISTS $table");
}

// Create 'continents' table
$sql = "CREATE TABLE continents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE
)";
if (!mysqli_query($conn, $sql)) die("Error creating continents table: " . mysqli_error($conn));

// Create 'countries' table
$sql = "CREATE TABLE countries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    continent_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    FOREIGN KEY (continent_id) REFERENCES continents(id) ON DELETE CASCADE
)";
if (!mysqli_query($conn, $sql)) die("Error creating countries table: " . mysqli_error($conn));

// Create 'meals' table
$sql = "CREATE TABLE meals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    continent_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    ingredients JSON,
    benefits TEXT,
    type VARCHAR(100),
    FOREIGN KEY (continent_id) REFERENCES continents(id) ON DELETE CASCADE
)";
if (!mysqli_query($conn, $sql)) die("Error creating meals table: " . mysqli_error($conn));

// Create 'local_fruits' table
$sql = "CREATE TABLE local_fruits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    continent_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    FOREIGN KEY (continent_id) REFERENCES continents(id) ON DELETE CASCADE
)";
if (!mysqli_query($conn, $sql)) die("Error creating local_fruits table: " . mysqli_error($conn));

// Create 'jobs' table
$sql = "CREATE TABLE jobs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type VARCHAR(255) NOT NULL UNIQUE,
    description TEXT,
    examples JSON,
    health_risks JSON,
    rec_exercise TEXT,
    rec_sleep TEXT,
    rec_diet_focus TEXT,
    rec_fruit_intake TEXT
)";
if (!mysqli_query($conn, $sql)) die("Error creating jobs table: " . mysqli_error($conn));

echo "All tables created successfully.\n";

// Insert Continents Data
foreach ($data['continents'] as $continentName => $continentData) {
    // Insert Continent
    $stmt = $conn->prepare("INSERT INTO continents (name) VALUES (?)");
    $stmt->bind_param("s", $continentName);
    $stmt->execute();
    $continentId = $stmt->insert_id;
    $stmt->close();

    // Insert Countries
    $stmt = $conn->prepare("INSERT INTO countries (continent_id, name) VALUES (?, ?)");
    foreach ($continentData['countries'] as $country) {
        $stmt->bind_param("is", $continentId, $country);
        $stmt->execute();
    }
    $stmt->close();

    // Insert Meals
    $stmt = $conn->prepare("INSERT INTO meals (continent_id, name, ingredients, benefits, type) VALUES (?, ?, ?, ?, ?)");
    foreach ($continentData['meals'] as $meal) {
        $ingredientsJson = json_encode($meal['ingredients']);
        $stmt->bind_param("issss", $continentId, $meal['name'], $ingredientsJson, $meal['benefits'], $meal['type']);
        $stmt->execute();
    }
    $stmt->close();

    // Insert Local Fruits
    $stmt = $conn->prepare("INSERT INTO local_fruits (continent_id, name) VALUES (?, ?)");
    foreach ($continentData['local_fruits'] as $fruit) {
        $stmt->bind_param("is", $continentId, $fruit);
        $stmt->execute();
    }
    $stmt->close();
}

// Insert Jobs Data
$stmt = $conn->prepare("INSERT INTO jobs (type, description, examples, health_risks, rec_exercise, rec_sleep, rec_diet_focus, rec_fruit_intake) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
foreach ($data['jobs'] as $jobType => $jobData) {
    $examplesJson = json_encode($jobData['examples']);
    $risksJson = json_encode($jobData['health_risks']);
    
    $stmt->bind_param("ssssssss", 
        $jobType, 
        $jobData['description'], 
        $examplesJson, 
        $risksJson, 
        $jobData['recommendations']['exercise'], 
        $jobData['recommendations']['sleep'], 
        $jobData['recommendations']['diet_focus'], 
        $jobData['recommendations']['fruit_intake']
    );
    $stmt->execute();
}
$stmt->close();

echo "Data migration completed successfully!\n";
?>
