import { getCsrfToken } from '../../../lib/csrf';

export default async function handler(req, res) {
  try {
    const token = await getCsrfToken();
    res.status(200).json({ token });
  } catch (error) {
    res.status(500).json({ error: 'Failed to fetch CSRF token' });
  }
}
