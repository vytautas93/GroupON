<?php
 
namespace GroupON\Repositories;
 
use Plenty\Exceptions\ValidationException;
use Plenty\Modules\Plugin\DataBase\Contracts\DataBase;
use GroupON\Contracts\GroupOnRepositoryContract;
use GroupON\Models\GroupOn;
use GroupON\Validators\GroupOnValidator;
use Plenty\Modules\Frontend\Services\AccountService;
 
use GroupON\Methods\GroupOnPickupDataMethod;
 
class GroupOnRepository implements GroupOnRepositoryContract
{
    /**
     * @var AccountService
     */
    private $accountService;
 
    /**
     * UserSession constructor.
     * @param AccountService $accountService
     */
    public function __construct(AccountService $accountService)
    {
        $this->accountService = $accountService;
    }
 
    /**
     * Get the current contact ID
     * @return int
     */
    public function getCurrentContactId(): int
    {
        return $this->accountService->getAccountContactId();
    }
 
    /**
     * Add a new item to the To Do list
     *
     * @param array $data
     * @return ToDo
     * @throws ValidationException
     */
    public function createGroupOnUser(array $data): GroupOn
    {
        try {
            GroupOnValidator::validateOrFail($data);
        } catch (ValidationException $e) {
            throw $e;
        }
 
        /**
         * @var DataBase $database
         */
        $database = pluginApp(DataBase::class);
 
        $groupOnUser = pluginApp(GroupOn::class);
 
        $groupOnUser->supplierID = $data['supplierID'];
        
        $groupOnUser->token = $data['token'];
 
        $groupOnUser->userId = $this->getCurrentContactId();
 
        $groupOnUser->createdAt = time();
 
        $database->save($groupOnUser);
 
        return $groupOnUser;
    }
 
    /**
     * List all items of the To Do list
     *
     * @return ToDo[]
     */
    public function getGroupOnList(): array
    {
        $database = pluginApp(DataBase::class);
 
        $id = $this->getCurrentContactId();
        /**
         * @var ToDo[] $toDoList
         */
        $groupOnUserList = $database->query(GroupOn::class)->where('userId', '=', $id)->get();
        return $groupOnUserList;
    }
 
    /**
     * Update the status of the item
     *
     * @param int $id
     * @return ToDo
     */
    public function updateGroupOnUser($id): GroupOn
    {
        /**
         * @var DataBase $database
         */
        $database = pluginApp(DataBase::class);
 
        $groupOnUserList = $database->query(GroupOn::class)
            ->where('id', '=', $id)
            ->get();
 
        $groupOnUser = $groupOnUserList[0];
        $groupOnUser->supplierID = 'test';
        $database->save($groupOnUser);
 
        return $groupOnUser;
    }
 
    /**
     * Delete an item from the To Do list
     *
     * @param int $id
     * @return ToDo
     */
    public function deleteGroupOnUser($id): GroupOn
    {
        /**
         * @var DataBase $database
         */
        $database = pluginApp(DataBase::class);
 
        $groupOnUserList = $database->query(GroupOn::class)
            ->where('id', '=', $id)
            ->get();
 
        $groupOnUser = $groupOnUserList[0];
        $database->delete($groupOnUser);
 
        return $groupOnUser;
    }
    
     public function test():string
    {
        $test = $this->getSupplierID();
        
        return $test;
    } 
    
    
    
}