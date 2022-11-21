<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\Tests\Rest\ResearchProject;

use Dbp\CampusonlineApi\Rest\Api;
use Dbp\CampusonlineApi\Rest\ResearchProject\ResearchProjectData;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class ResearchProjectTest extends TestCase
{
    private $api;

    protected function setUp(): void
    {
        $this->api = new Api('http://localhost', 'nope', 'nope');
        $this->api->getConnection()->setToken('foo');
        $this->mockResponses([]);
    }

    private function mockResponses(array $responses)
    {
        $stack = HandlerStack::create(new MockHandler($responses));
        $this->api->getConnection()->setClientHandler($stack);
    }

    public function testGetResearchProject()
    {
        $this->mockResponses([
            new Response(200, ['Content-Type' => 'application/json'], file_get_contents(__DIR__.'/research_project_api_response_item.json')),
        ]);

        $projectApi = $this->api->ResearchProject();
        $project = $projectApi->getResearchProject('F10427');
        $this->assertEquals('F10427', $project->getIdentifier());
        $this->assertEquals('Topographic and thematic maps for high mountain regions', $project->getTitle());
        $this->assertEquals('This is a test description', $project->getDescription());
        $this->assertEquals('1994-01-01', $project->getStartDate());
        $this->assertEquals('1996-01-31', $project->getEndDate());
    }

    public function testGetResearchProjectNotFound()
    {
        $this->mockResponses([
            new Response(200, ['Content-Type' => 'application/json'], file_get_contents(__DIR__.'/research_project_api_response_empty.json')),
        ]);

        $projectApi = $this->api->ResearchProject();
        $this->assertNull($projectApi->getResearchProject('__nowhere_to_be_found__'));
    }

    public function testGetResearchProjects()
    {
        $this->mockResponses([
            new Response(200, ['Content-Type' => 'application/json'], file_get_contents(__DIR__.'/research_project_api_response_collection.json')),
        ]);

        $projectApi = $this->api->ResearchProject();
        $projects = $projectApi->getResearchProjects(1, 30, [ResearchProjectData::TITLE_SEARCH_FILTER_NAME => 'goal']);
        $this->assertCount(5, $projects);
        $project = $projects[0];
        $this->assertEquals('F21899', $project->getIdentifier());
        $this->assertEquals('FWF - TRANSAGERE - Purpose Tagging: Capturing User Intent to Assist Goal-Oriented Social Search [Original in Deutsch: TransAgere-Agenten-orientierte Entwicklung Sozialer Software]', $project->getTitle());
        $this->assertEquals('The terms that are used by users during tagging have been found to be different from the terms that are used when searching for resources, which represents a fundamental problem for search in tagging based systems. To address this problem, we propose purpose tagging as a novel kind of tagging that focuses on capturing aspects of intent rather than content. By capturing the different purposes a given resource can serve, purpose tags appear useful to mediate between the vocabulary of user intent on one hand, and the vocabulary of contents and tags provided by social software applications on the other. The paper at hand makes the following contributions: 1) It extends the set of known kinds of tags with a novel type and 2) it provides first empirical evidence of the principle feasibility of purpose tagging and its potential to facilitate goal-oriented social search in an exploratory case study.', $project->getDescription());
        $this->assertEquals('2008-03-17', $project->getStartDate());
        $this->assertEquals('2010-09-17', $project->getEndDate());
    }

    public function testGetResearchProjectsEmpty()
    {
        $this->mockResponses([
            new Response(200, ['Content-Type' => 'application/json'], file_get_contents(__DIR__.'/research_project_api_response_empty.json')),
        ]);

        $projectApi = $this->api->ResearchProject();
        $projects = $projectApi->getResearchProjects(1, 30, [ResearchProjectData::TITLE_SEARCH_FILTER_NAME => 'not_found_in_any_title']);
        $this->assertEmpty($projects);
    }
}
