<?php
namespace CsrDelft\Orm;

use CsrDelft\Orm\Entity\PersistentEntity;

/**
 * Persistence.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Generic CRUD.
 */
interface Persistence {

	/**
	 * @param PersistentEntity $entity
	 * @return string last insert id
	 */
	public function create(PersistentEntity $entity);

	/**
	 * @param PersistentEntity $entity
	 * @return PersistentEntity|false
	 */
	public function retrieve(PersistentEntity $entity);

	/**
	 * @param PersistentEntity $entity
	 * @return int number of rows affected
	 */
	public function update(PersistentEntity $entity);

	/**
	 * @param PersistentEntity $entity
	 * @return int number of rows affected
	 */
	public function delete(PersistentEntity $entity);
}
