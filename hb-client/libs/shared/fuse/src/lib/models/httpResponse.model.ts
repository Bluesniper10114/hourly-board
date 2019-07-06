export interface CustomResponse {
  content?: any;
  success?: boolean;
  errors?: { message?: string, error?: string }[];
}
