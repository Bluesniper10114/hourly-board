import { Injectable } from '@angular/core';

@Injectable({
  providedIn: 'root'
})
export class SlugService {
  constructor() {}

  slugify(str: String) {
    return str
      .toString()
      .toLowerCase()
      .replace(/\s+/g, '-') // Replace spaces with -
      .replace(/[^\w\-]+/g, '') // Remove all non-word chars
      .replace(/\-\-+/g, '-') // Replace multiple - with single -
      .replace(/^-+/, '') // Trim - from start of text
      .replace(/-+$/, '-');
  }
}
