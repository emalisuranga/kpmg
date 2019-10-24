import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Router } from '@angular/router';
import { Observable } from 'rxjs';
import { APISocietyConnection } from './connections/APISocietyConnection';
import { IremoveSocietyBulkDoc, IgetSocietyBulkList, IgetSocietyRemovePending, IUpdateOptional, IremoveSociety } from '../models/society.model';

@Injectable({
  providedIn: 'root'
})
export class SocietyService {

  url: APISocietyConnection = new APISocietyConnection();

  constructor(private router: Router, private http: HttpClient) { }

  getBulk(data: IgetSocietyBulkList): Observable<IgetSocietyBulkList> {
    return this.http.post<IgetSocietyBulkList>(this.url.getSocietyBulkListURL(), data);
  }

  removeSocietyBulkFile(data: IremoveSocietyBulkDoc): Observable<IremoveSocietyBulkDoc> {
    return this.http.post<IremoveSocietyBulkDoc>(this.url.getRemoveFileURL(), data);
  }

  removeSocietyPendingAll(data: IgetSocietyRemovePending): Observable<IgetSocietyRemovePending> {
    return this.http.post<IgetSocietyRemovePending>(this.url.getRemovePendingSocietiesURL(), data);
  }

  updateOptionalInputs(data: IUpdateOptional): Observable<IUpdateOptional> {
    return this.http.post<IUpdateOptional>(this.url.updateOptionalURL(), data);
  }

  removeSociety(data: IremoveSociety): Observable<IremoveSociety> {
    return this.http.post<IremoveSociety>(this.url.removeSocietyURL(), data);
  }

}



