<?php
// Custom Helper Function 
function securePassword($username, $password)
{
    $password = md5($password);// Hash the password using MD5(which is a one-way hashing algorithm that produces a fixed-length string representation of the input password) to create a unique hash of the password. This adds an initial layer of security by transforming the original password into a non-reversible format.
    $salt = md5($username); // Generate a salt using the MD5 hash of the username. A salt is a random value added to the password before hashing to make it more resistant to attacks like rainbow table attacks. By using the username as part of the salt, it ensures that even if two users have the same password, their hashed passwords will be different due to the unique salt.
    $securePassword = crypt($password, $salt); // Further hash the password using bcrypt with the salt
    $securePassword = base64_encode($securePassword); // base64 turn the hashed password into a string format which is shorter and easier to store in the database. It also ensures that the hashed password can be safely transmitted over networks without issues related to special characters.
    return $securePassword;
}