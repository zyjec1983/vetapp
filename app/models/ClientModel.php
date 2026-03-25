<?php
// app/models/ClientModel.php

class ClientModel
{
    private $id_client;
    private $name;
    private $middlename;
    private $lastname1;
    private $lastname2;
    private $phone;
    private $email;
    private $address;
    private $identification;
    private $observations;
    private $active;
    private $created_at;
    

    public function __construct($data = [])
    {
        $this->hydrate($data);
    }

    public function hydrate(array $data)
    {
        foreach ($data as $key => $value) {
            $method = 'set' . str_replace('_', '', ucwords($key, '_'));
            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }
    }

    // Getters y Setters
    public function getIdClient() { return $this->id_client; }
    public function setIdClient($id) { $this->id_client = $id; }

    public function getName() { return $this->name; }
    public function setName($name) { $this->name = $name; }

    public function getMiddlename() { return $this->middlename; }
    public function setMiddlename($middlename) { $this->middlename = $middlename; }

    public function getLastname1() { return $this->lastname1; }
    public function setLastname1($lastname1) { $this->lastname1 = $lastname1; }

    public function getLastname2() { return $this->lastname2; }
    public function setLastname2($lastname2) { $this->lastname2 = $lastname2; }

    public function getFullName() {
        return trim($this->name . ' ' . $this->middlename . ' ' . $this->lastname1 . ' ' . $this->lastname2);
    }

    public function getPhone() { return $this->phone; }
    public function setPhone($phone) { $this->phone = $phone; }

    public function getEmail() { return $this->email; }
    public function setEmail($email) { $this->email = $email; }

    public function getAddress() { return $this->address; }
    public function setAddress($address) { $this->address = $address; }

    public function getIdentification() { return $this->identification; }
    public function setIdentification($identification) { $this->identification = $identification; }

    public function getObservations() { return $this->observations; }
    public function setObservations($observations) { $this->observations = $observations; }

    public function getActive() { return $this->active; }
    public function setActive($active) { $this->active = (bool) $active; }

    public function getCreatedAt() { return $this->created_at; }
    public function setCreatedAt($created_at) { $this->created_at = $created_at; }
}
?>

<?php
/**
 * Location: vetapp/app/models/ClientModel.php
 */

/*class ClientModel
{
    private $id_client;
    private $name;
    private $middlename;
    private $lastname1;
    private $lastname2;
    private $phone;
    private $email;
    private $address;
    private $identification;
    private $observations;
    private $created_at;

    public function __construct($data = [])
    {
        $this->hydrate($data);
    }

    public function hydrate($data)
    {
        foreach ($data as $key => $value) {
            $method = 'set' . ucfirst($key);
            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }
    }

    // Getters
    public function getIdClient() { return $this->id_client; }
    public function getName() { return $this->name; }
    public function getMiddlename() { return $this->middlename; }
    public function getLastname1() { return $this->lastname1; }
    public function getLastname2() { return $this->lastname2; }
    public function getPhone() { return $this->phone; }
    public function getEmail() { return $this->email; }
    public function getAddress() { return $this->address; }
    public function getIdentification() { return $this->identification; }
    public function getObservations() { return $this->observations; }
    public function getCreatedAt() { return $this->created_at; }

    // Setters
    public function setIdClient($id) { $this->id_client = $id; }
    public function setName($name) { $this->name = $name; }
    public function setMiddlename($middlename) { $this->middlename = $middlename; }
    public function setLastname1($lastname1) { $this->lastname1 = $lastname1; }
    public function setLastname2($lastname2) { $this->lastname2 = $lastname2; }
    public function setPhone($phone) { $this->phone = $phone; }
    public function setEmail($email) { $this->email = $email; }
    public function setAddress($address) { $this->address = $address; }
    public function setIdentification($identification) { $this->identification = $identification; }
    public function setObservations($observations) { $this->observations = $observations; }
    public function setCreatedAt($created_at) { $this->created_at = $created_at; }

    // Helper
    public function getFullName()
    {
        $full = $this->name;
        if ($this->middlename) $full .= ' ' . $this->middlename;
        $full .= ' ' . $this->lastname1;
        if ($this->lastname2) $full .= ' ' . $this->lastname2;
        return $full;
    }
}
    
*/

?>