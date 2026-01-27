<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\PublicRestApi\Appointments;

use Dbp\CampusonlineApi\PublicRestApi\AbstractApi;
use Dbp\CampusonlineApi\PublicRestApi\CursorBasedResourcePage;

class AppointmentApi extends AbstractApi
{
    public const EVENT_TYPE_KEY_QUERY_PARAMETER_NAME = 'event_type_key';
    public const COURSE_UID_QUERY_PARAMETER_NAME = 'course_uid';

    private const API_PATH = Common::API_PATH.'/appointments';

    public function getAppointmentByIdentifier(string $identifier): AppointmentResource
    {
        $resource = $this->getResourceByIdentifier(self::API_PATH,
            AppointmentResource::class, $identifier);
        assert($resource instanceof AppointmentResource);

        return $resource;
    }

    public function getAppointmentsCursorBased(array $queryParameters = [], ?string $cursor = null, int $maxNumItems = 30, array $options = []): CursorBasedResourcePage
    {
        return $this->getResourcesCursorBased(self::API_PATH,
            AppointmentResource::class, $queryParameters, $cursor, $maxNumItems);
    }

    public function getAppointmentsByCourseUidCursorBased(string $courseUid,
        array $queryParameters = [], ?string $cursor = null, int $maxNumItems = 30, array $options = []): CursorBasedResourcePage
    {
        $queryParameters[self::COURSE_UID_QUERY_PARAMETER_NAME] = $courseUid;

        return $this->getAppointmentsCursorBased($queryParameters, $cursor, $maxNumItems, $options);
    }
}
