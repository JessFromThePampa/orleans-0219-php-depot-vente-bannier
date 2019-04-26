<?php
/**
 * Created by PhpStorm.
 * User: sylvain
 * Date: 07/03/18
 * Time: 18:20
 * PHP version 7
 */

namespace App\Model;

use App\utils\CleanData;

/**
 *
 */
class ProductManager extends AbstractManager
{
    /**
     *
     */
    const TABLE = 'product';

    /**
     *  Initializes this class.
     */
    public function __construct()
    {
        parent::__construct(self::TABLE);
    }

    /**
     * Get all row from all the tables with join.
     *
     * @return array
     */
    public function showAllWithCategories(): array
    {
        $query = "SELECT product.id, product.name, product.price, product.date_added, product.date_saled, ahead
                    ,category.name AS categories 
                    FROM $this->table 
                    INNER JOIN bannier.category 
                    ON product.categories_id = category.id 
                    ORDER BY category.name ASC, product.name ASC;";
        return $this->pdo->query($query)->fetchAll();
    }

    public function showAllWithPictures(): array
    {
        $query = "SELECT product.id, product.name, product.price,  product.description, ahead
                    ,picture.name AS picture 
                    FROM $this->table 
                    INNER JOIN bannier.picture 
                    ON picture.product_id = product.id
                    ORDER BY product.id ASC
                    LIMIT 3;";
        return $this->pdo->query($query)->fetchAll();
    }

    /**
     * Select 3 random products ahead
     *
     * @return array
     */
    public function showAhead(): array
    {
        $query = "SELECT product.*
	                ,picture.name AS picture 
	                FROM $this->table 
	                INNER JOIN bannier.picture 
	                ON picture.product_id = product.id
	                WHERE product.ahead = 1
	                ORDER BY product.id ASC
	                LIMIT 3";
        return $this->pdo->query($query)->fetchAll();
    }

    /**
     * Select 3 random products
     *
     * @return array
     */
    public function showRandom(): array
    {
        $query = "SELECT product.name, picture.name AS image, product.description, product.price, product.date_saled
                    FROM picture
                    INNER JOIN bannier.product 
                    ON picture.product_id = product.id
                    ORDER BY RAND()
                    LIMIT 3";
        return $this->pdo->query($query)->fetchAll();
    }

    /**
     * Select all by id
     *
     * @return array
     */
    public function showAllById($id): array
    {
        $query = "SELECT product.id, product.name, product.price,  product.description,
                    ahead, product.date_added, product.date_saled, category.name as category
                    FROM $this->table
                   INNER JOIN bannier.category
                    ON product.categories_id = category.id
                    WHERE product.id = $id";
        return $this->pdo->query($query)->fetchAll();
    }

    /**
     * Select images of one product
     *
     * @return array
     */
    public function showProductImagesById($id): array
    {
        $query = "SELECT product.id, picture.name
                    FROM $this->table
                    INNER JOIN bannier.picture
                    ON picture.product_id = product.id
                    WHERE product.id = $id";
        return $this->pdo->query($query)->fetchAll();
    }

    /**
     *
     *  insert new product in BDD
     *
     * @param array $data
     * @return int
     */
    public function insert(array $data): int
    {

        $query = "INSERT INTO $this->table 
                (`name`, `categories_id`, `description`, `price`, 
                `date_added`, `ahead`)
                VALUES (:name, :categories_id, :description, :price, 
                :date_added, :ahead)";

        $statement = $this->pdo->prepare($query);
        $statement->bindValue('name', $data['name'], \PDO::PARAM_STR);
        $statement->bindValue('categories_id', $data['categories_id'], \PDO::PARAM_STR);
        $statement->bindValue('description', $data['description'], \PDO::PARAM_STR);
        $statement->bindValue('price', $data['price'], \PDO::PARAM_STR);
        $statement->bindValue('date_added', $data['date_added'], \PDO::PARAM_STR);
        $statement->bindValue('ahead', $data['ahead'], \PDO::PARAM_STR);

        if ($statement->execute()) {
            $id = (int)$this->pdo->lastInsertId();
            return $id;
        }
    }
}
