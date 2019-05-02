<?php
/**
 * Created by PhpStorm.
 * User: sylvain
 * Date: 07/03/18
 * Time: 18:20
 * PHP version 7
 */

namespace App\Model;

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
                    JOIN bannier.category
                    ON product.categories_id = category.id
                    ORDER BY category.name ASC, product.name ASC";
        return $this->pdo->query($query)->fetchAll();
    }

    /**
     * Show all products with pictures Association
     *
     * @return array
     */
    public function showAllWithPictures(): array
    {
        $query = "SELECT DISTINCT pr.id, pr.name, pr.price,  pr.description, pr.ahead ,min(pi.name) AS picture 
                    FROM $this->table pr
                    JOIN picture pi ON pi.product_id = pr.id
                    GROUP BY pr.id
                    ORDER BY pr.id ASC";
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
	                INNER JOIN picture 
	                ON product_id = product.id
	                WHERE product.ahead = 1
	                ORDER BY product.id ASC
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
                   INNER JOIN category
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
        $query = "SELECT picture.id, picture.name
	                FROM picture
	                WHERE product_id = $id";

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

    /**
     * Delete product from BDD
     *
     * @param int $id
     */
    public function delete(int $id): void
    {

        $query = "DELETE FROM $this->table WHERE `id`=$id";
        $statement = $this->pdo->prepare($query);
        $statement->execute();
    }

    /**
     * Select Products filtered by categories
     *
     * @param int $id
     * @return array
     */
    public function productsFiltered(int $id = null, string $search = null, string $sort = null): array
    {
        $query = "SELECT *, picture.name AS image, category.name AS categories, product.date_added,
                    product.price, product.name
	                FROM $this->table
	                INNER JOIN picture ON picture.product_id = product.id
	                INNER JOIN category ON product.categories_id = category.id
	                WHERE 1=1 ";
        if ($id) {
            $query .= " AND product.categories_id =:id";
        }

        if ($search) {
            $query .= " AND product.name LIKE :search";
        }

        if ($sort === 'ascPrice') {
            $query .= " ORDER BY product.price ASC";
        }

        if ($sort === 'descPrice') {
            $query .= " ORDER BY product.price DESC";
        }

        if ($sort === 'ascDateAdded') {
            $query .= " ORDER BY product.date_added ASC";
        }

        if ($sort === 'descDateAdded') {
            $query .= " ORDER BY product.date_added DESC";
        }

        $statement = $this->pdo->prepare($query);

        if ($id) {
            $statement->bindValue('id', $id, \PDO::PARAM_INT);
        }

        if ($search) {
            $statement->bindValue('search', '%'. $search .'%', \PDO::PARAM_STR);
        }

        $statement->execute();

        return $statement->fetchAll();
    }

    /**
     * Select 3 random products
     *
     * @return array
     */
    public function showRandom(int $id): array
    {
        $query = "SELECT product.name, picture.name AS image, product.description,
                    product.price, product.date_saled, category.name AS category
                    FROM picture
                    INNER JOIN product ON picture.product_id = product.id
                    INNER JOIN category ON product.categories_id = category.id
                    WHERE categories_id = $id
                    ORDER BY RAND()
                    LIMIT 3";
        return $this->pdo->query($query)->fetchAll();
    }
}
