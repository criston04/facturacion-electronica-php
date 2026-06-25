<?php
class Database
{
	private $con;

	public function connect()
	{
		$this->con = new mysqli(
			"localhost",   // host
			"root",        // usuario
			"",            // contraseña (Laragon/XAMPP por defecto: vacía)
			"facturacion_electronica",        // base de datos
			3306           // puerto MySQL
		);

		if ($this->con->connect_error) {
			die("Error de conexión: " . $this->con->connect_error);
		}

		mysqli_set_charset($this->con, "utf8");
		return $this->con;
	}
}
