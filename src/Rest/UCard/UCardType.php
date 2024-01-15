<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\Rest\UCard;

class UCardType
{
    public const PERSON_GROUP_STAFF = 'B'; // Bedienstete
    public const PERSON_GROUP_STUDENTS = 'ST'; // Studierende
    public const PERSON_GROUP_EXTERNAL = 'E'; // Extern
    public const PERSON_GROUP_IDENTITY = 'I'; // Identitaet

    public const CARD_SUBTYPE_PERSONAL_RESOURCE_CARD = 'PR'; // Personengebundene Ressourcenkarte
    public const CARD_SUBTYPE_ID_CARD = 'A'; // Ausweis
    public const CARD_SUBTYPE_RESOURCE_CARD = 'R'; // Ressourcenkarte

    public const STA = self::PERSON_GROUP_STUDENTS.self::CARD_SUBTYPE_ID_CARD;
    public const BA = self::PERSON_GROUP_STAFF.self::CARD_SUBTYPE_ID_CARD;
    public const EPR = self::PERSON_GROUP_EXTERNAL.self::CARD_SUBTYPE_PERSONAL_RESOURCE_CARD;
    public const IR = self::PERSON_GROUP_IDENTITY.self::CARD_SUBTYPE_RESOURCE_CARD;
    public const BPR = self::PERSON_GROUP_STAFF.self::CARD_SUBTYPE_PERSONAL_RESOURCE_CARD;
}
