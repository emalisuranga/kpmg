import { Injectable } from '@angular/core';
import { InMemoryDbService } from 'angular-in-memory-web-api';

@Injectable({
  providedIn: 'root'
})
export class CancelInMemoryDataServiceService implements InMemoryDbService {

  createDb() {
    const reason = [
      { id: 1,  description: 'Do you want to receive the pushnotificationabout the newest posts' },
      { id: 2,  description: 'Do you want to receive the pushnotificationabout the newest posts' },
      { id: 3,  description: 'Do you want to receive the pushnotificationabout the newest posts' },
      { id: 4,  description: 'Do you want to receive the pushnotificationabout the newest posts' },
      { id: 5,  description: 'Other' },
    ];
    return { reason };
  }
}
