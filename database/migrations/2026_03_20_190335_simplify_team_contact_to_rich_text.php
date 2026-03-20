<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add the new rich text field first
        Schema::table('teams', function (Blueprint $table) {
            $table->longText('contact_content')->nullable();
        });

        // Migrate existing data to rich text format using raw queries
        $teams = DB::table('teams')->get();
        foreach ($teams as $team) {
            $content = $this->buildRichTextContentFromRaw($team);
            DB::table('teams')
                ->where('id', $team->id)
                ->update(['contact_content' => $content]);
        }

        // Then remove old individual fields
        Schema::table('teams', function (Blueprint $table) {
            $table->dropColumn([
                'contact_description',
                'phone',
                'address_line_1',
                'address_line_2',
                'address_line_3',
                'office_hours',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            // Add back the old fields
            $table->text('contact_description')->nullable();
            $table->string('phone')->nullable();
            $table->string('address_line_1')->nullable();
            $table->string('address_line_2')->nullable();
            $table->string('address_line_3')->nullable();
            $table->text('office_hours')->nullable();

            // Remove the rich text field
            $table->dropColumn('contact_content');
        });
    }

    /**
     * Build default rich text content from existing fields
     */
    private function buildRichTextContentFromRaw($team): string
    {
        $content = '<div>';

        if ($team->contact_description) {
            $content .= '<p>'.htmlspecialchars($team->contact_description).'</p>';
        }

        // Default content with location (no hardcoded department contact)
        if ($team->address_line_1 || $team->address_line_2 || $team->address_line_3) {
            $content .= '<p><strong>Location:</strong><br>';
            if ($team->address_line_1) {
                $content .= htmlspecialchars($team->address_line_1).'<br>';
            }
            if ($team->address_line_2) {
                $content .= htmlspecialchars($team->address_line_2).'<br>';
            }
            if ($team->address_line_3) {
                $content .= htmlspecialchars($team->address_line_3);
            }
            $content .= '</p>';
        } else {
            $content .= '<p><strong>Location:</strong><br>';
            $content .= 'Carnegie Mellon University<br>';
            $content .= 'Mellon College of Science<br>';
            $content .= 'Pittsburgh, PA 15213</p>';
        }

        if ($team->phone) {
            $content .= '<p><strong>Phone:</strong><br>';
            $content .= '<a href="tel:'.htmlspecialchars($team->phone).'">'.htmlspecialchars($team->phone).'</a></p>';
        }

        if ($team->office_hours) {
            $content .= '<p><strong>Office Hours:</strong><br>';
            $content .= nl2br(htmlspecialchars($team->office_hours)).'</p>';
        }

        $content .= '</div>';

        return $content;
    }
};
