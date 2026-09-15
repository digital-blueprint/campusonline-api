<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\PublicRestApi\Curriculum;

use Dbp\CampusonlineApi\PublicRestApi\AbstractApi;
use Dbp\CampusonlineApi\PublicRestApi\CursorBasedResourcePage;

class ModuleApi extends AbstractApi
{
    public const CURRICULUM_VERSION_UID_QUERY_PARAMETER_NAME = 'curriculum_version_uid';

    private const API_PATH = Common::API_PATH.'/curriculum/api/curriculum-elements/modules';

    public function getModulesByCurriculumVersionUidCursorBased(string $curriculumVersionUid,
        ?string $cursor = null, int $maxNumItems = 30, array $options = []): CursorBasedResourcePage
    {
        $queryParameters = [
            self::CURRICULUM_VERSION_UID_QUERY_PARAMETER_NAME => $curriculumVersionUid,
        ];

        return $this->getResourcesCursorBased(self::API_PATH,
            ModuleResource::class, $queryParameters, $cursor, $maxNumItems);
    }
}
