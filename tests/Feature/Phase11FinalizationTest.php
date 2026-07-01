<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

it('removes legacy compatibility columns after finalization', function () {
    expect(Schema::hasColumn('projectrequests', 'projectid'))->toBeFalse();
    expect(Schema::hasColumn('projectrequests', 'oneid'))->toBeFalse();
    expect(Schema::hasColumn('ideas', 'supname'))->toBeFalse();
    expect(Schema::hasColumn('ideas', 'oneid'))->toBeFalse();
    expect(Schema::hasColumn('ideas', 'projectname'))->toBeTrue();
    expect(Schema::hasColumn('contacts', 'email'))->toBeFalse();
    expect(Schema::hasColumn('contacts', 'supname'))->toBeFalse();
    expect(Schema::hasColumn('supcontacts', 'projectname'))->toBeFalse();
    expect(Schema::hasColumn('project_submissions', 'student_email'))->toBeFalse();
    expect(Schema::hasColumn('project_submissions', 'student_name'))->toBeFalse();
});
