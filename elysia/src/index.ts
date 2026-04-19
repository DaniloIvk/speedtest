import { SQL } from 'bun';
import { Elysia, t } from 'elysia';

const sql = new SQL(Bun.env.DB_URL!);

new Elysia()
    .post(
        '/api/request-access',
        async ({body, set}) => {
            const [cardIdent, deviceIdent] = body.split('|');
            const payloadIsValid = Boolean(cardIdent && deviceIdent);

            const [card] = payloadIsValid
                ? await sql`SELECT id
                            FROM cards
                            WHERE identifier = ${cardIdent}
                              AND deleted_at IS NULL
                            LIMIT 1`
                : [];

            const [device] = payloadIsValid
                ? await sql`
                    SELECT d.id, a.path
                    FROM devices d
                             LEFT JOIN areas a ON a.id = d.area_id
                    WHERE d.identifier = ${deviceIdent}
                      AND d.deleted_at IS NULL
                    LIMIT 1`
                : [];

            const requestIsValid = Boolean(payloadIsValid && card && device);
            const accessGranted =
                requestIsValid &&
                typeof device.path === 'string' &&
                device.path.includes(`/${card.id}/`);

            if (requestIsValid) {
                await sql`
                    INSERT INTO activity_log
                    (causer_type, causer_id, subject_type, subject_id, event, log_name, description, properties,
                     created_at, updated_at)
                    VALUES (${'App\\Models\\Card'}, ${card.id}, ${'App\\Models\\Device'}, ${device.id},
                            'access', 'access_required', 'access_required',
                            ${JSON.stringify({access_granted: accessGranted})}, NOW(), NOW())`;
            } else {
                await sql`
                    INSERT INTO activity_log
                    (causer_type, causer_id, subject_type, subject_id, event, log_name, description, properties,
                     created_at, updated_at)
                    VALUES (NULL, NULL, NULL, NULL, 'invalid_access', 'access_required', 'access_required', NULL, NOW(),
                            NOW())`;
            }

            set.status = accessGranted ? 200 : 403;
            return accessGranted;
        },
        {parse: 'text', body: t.String()},
    )
    .listen(8000);

console.log('Elysia is running at :8000');
