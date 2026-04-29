<?php declare(strict_types=1);
namespace hiperesp\server\services;

use hiperesp\server\attributes\Inject;
use hiperesp\server\exceptions\DFException;
use hiperesp\server\models\CharacterModel;
use hiperesp\server\models\UserModel;
use hiperesp\server\storage\Storage;
use hiperesp\server\vo\CharacterVO;
use hiperesp\server\vo\UserVO;

class AdminService extends Service {

    #[Inject] private Storage $storage;
    #[Inject] private UserModel $userModel;
    #[Inject] private CharacterModel $characterModel;

    /** @return array<UserVO> */
    public function getAllUsers(): array {
        $rows = $this->storage->select('user', [], null);
        return \array_map(fn($r) => new UserVO($r), $rows);
    }

    /** @return array<UserVO> */
    public function searchUsers(string $query): array {
        $query = \strtolower(\trim($query));
        return \array_values(\array_filter(
            $this->getAllUsers(),
            fn($u) => \str_contains(\strtolower($u->username), $query)
                   || \str_contains(\strtolower($u->email), $query)
        ));
    }

    public function getUserById(int $id): UserVO {
        return $this->userModel->getById($id);
    }

    public function getCharById(int $id): CharacterVO {
        return $this->characterModel->getById($id);
    }

    /** @return array<CharacterVO> */
    public function getCharsByUser(UserVO $user): array {
        return $this->characterModel->getByUser($user);
    }

    public function updateChar(int $charId, array $fields): void {
        $allowed = ['gold', 'coins', 'gems', 'silver', 'dragonAmulet', 'level', 'experience', 'bagSlots', 'bankSlots'];
        $update = ['id' => $charId];
        foreach ($allowed as $field) {
            if (\array_key_exists($field, $fields)) {
                $update[$field] = $fields[$field];
            }
        }
        if (\count($update) > 1) {
            $this->storage->update('char', $update);
        }
    }

    public function updateUser(int $userId, array $fields): void {
        $allowed = ['upgraded', 'banned', 'activated'];
        $update = ['id' => $userId];
        foreach ($allowed as $field) {
            if (\array_key_exists($field, $fields)) {
                $update[$field] = $fields[$field];
            }
        }
        if (\count($update) > 1) {
            $this->storage->update('user', $update);
        }
    }

    public function getSettings(): array {
        global $config;
        $id = (int)($config['DF_SETTINGS_ID'] ?? 1);
        $rows = $this->storage->select('settings', ['id' => $id]);
        return $rows[0] ?? [];
    }

    public function updateSettings(array $fields): void {
        global $config;
        $id = (int)($config['DF_SETTINGS_ID'] ?? 1);

        $allowed = [
            'serverName', 'news', 'signUpMessage',
            'experienceMultiplier', 'goldMultiplier', 'gemsMultiplier', 'silverMultiplier',
            'dailyQuestCoinsReward',
            'dragonAmuletForAll', 'enableAdvertising',
            'revalidateClientValues', 'banInvalidClientValues', 'canDeleteUpgradedChar',
            'nonUpgradedChars', 'upgradedChars',
            'nonUpgradedMaxBagSlots', 'upgradedMaxBagSlots',
            'nonUpgradedMaxBankSlots', 'upgradedMaxBankSlots',
            'nonUpgradedMaxHouseSlots', 'upgradedMaxHouseSlots',
            'nonUpgradedMaxHouseItemSlots', 'upgradedMaxHouseItemSlots',
            'onlineThreshold',
            'sendEmails', 'emailApiUrl', 'emailApiToken', 'emailAddress',
        ];

        $update = ['id' => $id];
        foreach ($allowed as $field) {
            if (\array_key_exists($field, $fields)) {
                $update[$field] = $fields[$field];
            }
        }
        if (\count($update) > 1) {
            $this->storage->update('settings', $update);
        }
    }
}
