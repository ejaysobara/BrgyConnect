-- Migration: non-health appointments must not point at health_services.
-- Storing 0 in health_service_id violates the foreign key
-- (appointments_ibfk_2); NULL is the correct "no health service" value.
-- The application also applies this automatically on the next booking.

ALTER TABLE appointments MODIFY health_service_id INT NULL DEFAULT NULL;

-- Clean up any legacy rows created before the foreign key existed.
UPDATE appointments SET health_service_id = NULL WHERE health_service_id = 0;
