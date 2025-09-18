<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPerformanceOptimizationIndexes extends Migration
{
    public function up()
    {
        // Meeting Attendance Query Optimization Indexes
        $this->db->query("ALTER TABLE meeting_attendances ADD INDEX idx_meeting_attendance_status (meeting_id, attendance_type, property_owner_id)");
        $this->db->query("ALTER TABLE meeting_attendances ADD INDEX idx_meeting_calculated_attendance (meeting_id, is_calculated, attendance_type)");
        $this->db->query("ALTER TABLE meeting_attendances ADD INDEX idx_meeting_calculated_checkin (meeting_id, is_calculated, check_in_time)");

        // Voting Statistics Query Optimization Indexes
        $this->db->query("ALTER TABLE voting_records ADD INDEX idx_voting_topic_choice_time (voting_topic_id, vote_choice, vote_time)");
        $this->db->query("ALTER TABLE voting_records ADD INDEX idx_owner_vote_choice_time (property_owner_id, vote_choice, vote_time)");
        $this->db->query("ALTER TABLE voting_records ADD INDEX idx_voting_area_weights (voting_topic_id, land_area_weight, building_area_weight)");

        // Area Weight Calculation Optimization Indexes
        $this->db->query("ALTER TABLE owner_land_ownership ADD INDEX idx_land_ownership_calculation (property_owner_id, ownership_numerator, ownership_denominator)");
        $this->db->query("ALTER TABLE owner_building_ownership ADD INDEX idx_building_ownership_calculation (property_owner_id, ownership_numerator, ownership_denominator)");

        // Meeting and Urban Renewal Relationship Optimization
        $this->db->query("ALTER TABLE meetings ADD INDEX idx_urban_renewal_meeting_status_date (urban_renewal_id, meeting_status, meeting_date)");
        $this->db->query("ALTER TABLE meetings ADD INDEX idx_status_date_urban_renewal (meeting_status, meeting_date, urban_renewal_id)");

        // Property Owner Search Optimization
        $this->db->query("ALTER TABLE property_owners ADD INDEX idx_urban_renewal_name_exclusion (urban_renewal_id, name(50), exclusion_type)");
        $this->db->query("ALTER TABLE property_owners ADD INDEX idx_urban_renewal_exclusion_deleted (urban_renewal_id, exclusion_type, deleted_at)");

        // Voting Topic and Meeting Relationship Optimization
        $this->db->query("ALTER TABLE voting_topics ADD INDEX idx_meeting_voting_status_result (meeting_id, voting_status, voting_result)");

        // Land Plot Location Search Optimization
        $this->db->query("ALTER TABLE land_plots ADD INDEX idx_urban_renewal_location_full (urban_renewal_id, county, district, section)");
        $this->db->query("ALTER TABLE land_plots ADD INDEX idx_representative_urban_area (is_representative, urban_renewal_id, land_area)");

        // Building Location Search Optimization
        $this->db->query("ALTER TABLE buildings ADD INDEX idx_building_urban_renewal_location (urban_renewal_id, county, district, section)");
        $this->db->query("ALTER TABLE buildings ADD INDEX idx_urban_area_address (urban_renewal_id, building_area, building_address(100))");

        // Meeting Documents Performance
        $this->db->query("ALTER TABLE meeting_documents ADD INDEX idx_meeting_document_type_date (meeting_id, document_type, created_at)");

        // Meeting Logs Performance
        $this->db->query("ALTER TABLE meeting_logs ADD INDEX idx_meeting_action_date (meeting_id, action_type, created_at)");

        // User Sessions Performance
        $this->db->query("ALTER TABLE user_sessions ADD INDEX idx_user_expires_active (user_id, expires_at, is_active)");

        // Notifications Performance
        $this->db->query("ALTER TABLE notifications ADD INDEX idx_user_type_read_date (user_id, notification_type, is_read, created_at)");
        $this->db->query("ALTER TABLE notifications ADD INDEX idx_urban_renewal_global_expires (urban_renewal_id, is_global, expires_at)");

        // System Settings Performance
        $this->db->query("ALTER TABLE system_settings ADD INDEX idx_category_public_order (category, is_public, display_order)");

        // Full-text Search Indexes for Better Search Performance
        $this->db->query("ALTER TABLE property_owners ADD FULLTEXT idx_property_owners_search (name, contact_address, household_address, notes)");
        $this->db->query("ALTER TABLE meetings ADD FULLTEXT idx_meetings_search (meeting_name, meeting_location)");
        $this->db->query("ALTER TABLE voting_topics ADD FULLTEXT idx_voting_topics_search (topic_title, topic_description)");
        $this->db->query("ALTER TABLE meeting_notices ADD FULLTEXT idx_meeting_notices_search (chairman_name, contact_person)");

        // Create triggers for automatic statistics updates
        $this->db->query("
            CREATE TRIGGER trg_meeting_attendance_update_stats
            AFTER INSERT ON meeting_attendances
            FOR EACH ROW
            BEGIN
                UPDATE meetings
                SET attendee_count = (
                    SELECT COUNT(*)
                    FROM meeting_attendances
                    WHERE meeting_id = NEW.meeting_id
                    AND attendance_type IN ('present', 'proxy')
                    AND is_calculated = 1
                )
                WHERE id = NEW.meeting_id;
            END
        ");

        $this->db->query("
            CREATE TRIGGER trg_meeting_attendance_update_stats_on_update
            AFTER UPDATE ON meeting_attendances
            FOR EACH ROW
            BEGIN
                UPDATE meetings
                SET attendee_count = (
                    SELECT COUNT(*)
                    FROM meeting_attendances
                    WHERE meeting_id = NEW.meeting_id
                    AND attendance_type IN ('present', 'proxy')
                    AND is_calculated = 1
                )
                WHERE id = NEW.meeting_id;
            END
        ");

        $this->db->query("
            CREATE TRIGGER trg_voting_record_update_stats
            AFTER INSERT ON voting_records
            FOR EACH ROW
            BEGIN
                UPDATE voting_topics
                SET
                    total_votes = (SELECT COUNT(*) FROM voting_records WHERE voting_topic_id = NEW.voting_topic_id),
                    agree_votes = (SELECT COUNT(*) FROM voting_records WHERE voting_topic_id = NEW.voting_topic_id AND vote_choice = 'agree'),
                    disagree_votes = (SELECT COUNT(*) FROM voting_records WHERE voting_topic_id = NEW.voting_topic_id AND vote_choice = 'disagree'),
                    abstain_votes = (SELECT COUNT(*) FROM voting_records WHERE voting_topic_id = NEW.voting_topic_id AND vote_choice = 'abstain'),
                    agree_land_area = (SELECT COALESCE(SUM(land_area_weight), 0) FROM voting_records WHERE voting_topic_id = NEW.voting_topic_id AND vote_choice = 'agree'),
                    disagree_land_area = (SELECT COALESCE(SUM(land_area_weight), 0) FROM voting_records WHERE voting_topic_id = NEW.voting_topic_id AND vote_choice = 'disagree'),
                    abstain_land_area = (SELECT COALESCE(SUM(land_area_weight), 0) FROM voting_records WHERE voting_topic_id = NEW.voting_topic_id AND vote_choice = 'abstain'),
                    agree_building_area = (SELECT COALESCE(SUM(building_area_weight), 0) FROM voting_records WHERE voting_topic_id = NEW.voting_topic_id AND vote_choice = 'agree'),
                    disagree_building_area = (SELECT COALESCE(SUM(building_area_weight), 0) FROM voting_records WHERE voting_topic_id = NEW.voting_topic_id AND vote_choice = 'disagree'),
                    abstain_building_area = (SELECT COALESCE(SUM(building_area_weight), 0) FROM voting_records WHERE voting_topic_id = NEW.voting_topic_id AND vote_choice = 'abstain')
                WHERE id = NEW.voting_topic_id;
            END
        ");

        $this->db->query("
            CREATE TRIGGER trg_voting_record_update_stats_on_update
            AFTER UPDATE ON voting_records
            FOR EACH ROW
            BEGIN
                UPDATE voting_topics
                SET
                    total_votes = (SELECT COUNT(*) FROM voting_records WHERE voting_topic_id = NEW.voting_topic_id),
                    agree_votes = (SELECT COUNT(*) FROM voting_records WHERE voting_topic_id = NEW.voting_topic_id AND vote_choice = 'agree'),
                    disagree_votes = (SELECT COUNT(*) FROM voting_records WHERE voting_topic_id = NEW.voting_topic_id AND vote_choice = 'disagree'),
                    abstain_votes = (SELECT COUNT(*) FROM voting_records WHERE voting_topic_id = NEW.voting_topic_id AND vote_choice = 'abstain'),
                    agree_land_area = (SELECT COALESCE(SUM(land_area_weight), 0) FROM voting_records WHERE voting_topic_id = NEW.voting_topic_id AND vote_choice = 'agree'),
                    disagree_land_area = (SELECT COALESCE(SUM(land_area_weight), 0) FROM voting_records WHERE voting_topic_id = NEW.voting_topic_id AND vote_choice = 'disagree'),
                    abstain_land_area = (SELECT COALESCE(SUM(land_area_weight), 0) FROM voting_records WHERE voting_topic_id = NEW.voting_topic_id AND vote_choice = 'abstain'),
                    agree_building_area = (SELECT COALESCE(SUM(building_area_weight), 0) FROM voting_records WHERE voting_topic_id = NEW.voting_topic_id AND vote_choice = 'agree'),
                    disagree_building_area = (SELECT COALESCE(SUM(building_area_weight), 0) FROM voting_records WHERE voting_topic_id = NEW.voting_topic_id AND vote_choice = 'disagree'),
                    abstain_building_area = (SELECT COALESCE(SUM(building_area_weight), 0) FROM voting_records WHERE voting_topic_id = NEW.voting_topic_id AND vote_choice = 'abstain')
                WHERE id = NEW.voting_topic_id;
            END
        ");
    }

    public function down()
    {
        // Drop triggers
        $this->db->query("DROP TRIGGER IF EXISTS trg_voting_record_update_stats_on_update");
        $this->db->query("DROP TRIGGER IF EXISTS trg_voting_record_update_stats");
        $this->db->query("DROP TRIGGER IF EXISTS trg_meeting_attendance_update_stats_on_update");
        $this->db->query("DROP TRIGGER IF EXISTS trg_meeting_attendance_update_stats");

        // Drop fulltext indexes
        $this->db->query("ALTER TABLE meeting_notices DROP INDEX idx_meeting_notices_search");
        $this->db->query("ALTER TABLE voting_topics DROP INDEX idx_voting_topics_search");
        $this->db->query("ALTER TABLE meetings DROP INDEX idx_meetings_search");
        $this->db->query("ALTER TABLE property_owners DROP INDEX idx_property_owners_search");

        // Drop performance indexes
        $this->db->query("ALTER TABLE system_settings DROP INDEX idx_category_public_order");
        $this->db->query("ALTER TABLE notifications DROP INDEX idx_urban_renewal_global_expires");
        $this->db->query("ALTER TABLE notifications DROP INDEX idx_user_type_read_date");
        $this->db->query("ALTER TABLE user_sessions DROP INDEX idx_user_expires_active");
        $this->db->query("ALTER TABLE meetings DROP INDEX idx_status_date_urban_renewal");
        $this->db->query("ALTER TABLE voting_records DROP INDEX idx_voting_area_weights");
        $this->db->query("ALTER TABLE property_owners DROP INDEX idx_urban_renewal_exclusion_deleted");
        $this->db->query("ALTER TABLE meeting_attendances DROP INDEX idx_meeting_calculated_checkin");
        $this->db->query("ALTER TABLE meeting_logs DROP INDEX idx_meeting_action_date");
        $this->db->query("ALTER TABLE meeting_documents DROP INDEX idx_meeting_document_type_date");
        $this->db->query("ALTER TABLE buildings DROP INDEX idx_building_urban_renewal_location");
        $this->db->query("ALTER TABLE land_plots DROP INDEX idx_urban_renewal_location_full");
        $this->db->query("ALTER TABLE voting_topics DROP INDEX idx_meeting_voting_status_result");
        $this->db->query("ALTER TABLE property_owners DROP INDEX idx_urban_renewal_name_exclusion");
        $this->db->query("ALTER TABLE meetings DROP INDEX idx_urban_renewal_meeting_status_date");
        $this->db->query("ALTER TABLE owner_building_ownership DROP INDEX idx_building_ownership_calculation");
        $this->db->query("ALTER TABLE owner_land_ownership DROP INDEX idx_land_ownership_calculation");
        $this->db->query("ALTER TABLE voting_records DROP INDEX idx_owner_vote_choice_time");
        $this->db->query("ALTER TABLE voting_records DROP INDEX idx_voting_topic_choice_time");
        $this->db->query("ALTER TABLE meeting_attendances DROP INDEX idx_meeting_calculated_attendance");
        $this->db->query("ALTER TABLE meeting_attendances DROP INDEX idx_meeting_attendance_status");
        $this->db->query("ALTER TABLE buildings DROP INDEX idx_urban_area_address");
        $this->db->query("ALTER TABLE land_plots DROP INDEX idx_representative_urban_area");
    }
}