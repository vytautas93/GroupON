<?php
 
namespace GroupON\Contracts;
 
use GroupON\Models\GroupOn;
 
/**
 * Class GroupOnRepositoryContract
 * @package ToDoList\Contracts
 */
interface GroupOnRepositoryContract
{
    /**
     * Add a new shop to the GroupOn List
     *
     * @param array $data
     * @return ToDo
     */
    public function createGroupOnUser(array $data): GroupOn;
 
    /**
     * List all tasks of the To Do list
     *
     * @return ToDo[]
     */
    public function getGroupOnList(): array;
    
    public function test(): array;
 
    /**
     * Update the status of the task
     *
     * @param int $id
     * @return ToDo
     */
    public function updateGroupOnUser($id): GroupOn;
 
    /**
     * Delete a task from the To Do list
     *
     * @param int $id
     * @return ToDo
     */
    public function deleteGroupOnUser($id): GroupOn;
}