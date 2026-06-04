const API_BASE_URL = 'http://localhost/rem.s/rems/api';

export interface ApiResponse<T> {
  data?: T;
  error?: string;
}

export async function apiRequest<T>(
  endpoint: string,
  options: RequestInit = {}
): Promise<ApiResponse<T>> {
  try {
    const response = await fetch(`${API_BASE_URL}${endpoint}`, {
      credentials: 'include',
      headers: {
        'Content-Type': 'application/json',
        ...options.headers,
      },
      ...options,
    });

    if (!response.ok) {
      const error = await response.text();
      return { error };
    }

    const data = await response.json();
    return { data };
  } catch (error) {
    return { error: error instanceof Error ? error.message : 'Unknown error' };
  }
}

export async function getProperties(status?: string) {
  const params = status ? `?status=${status}` : '';
  return apiRequest<Property[]>('/properties.php' + params);
}

export async function createProperty(property: Omit<Property, 'id'>) {
  return apiRequest<{ id: string; message: string }>('/properties.php', {
    method: 'POST',
    body: JSON.stringify(property),
  });
}

// Add more API functions as needed