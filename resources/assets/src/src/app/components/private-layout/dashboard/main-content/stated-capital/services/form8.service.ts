import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Router } from '@angular/router';
import { Observable } from 'rxjs';
import { APIform8Connection } from './connections/APIform8Connection';
import { IIncorporationData, IIncorporationDataStep1Data } from '../../../../../../http/models/incorporation.model';
import { INICchecker } from '../../../../../../http/models/stakeholder.model';
import {  IremoveAnnualDoc, Iresubmit, ISubmitCallRecords, IremoveCallShareDoc, ISubmitRegisterOfChargesRecords, IRemoveOtherDoc, ISubmitCapital, IupdateCourtDetails } from '../models/form8Model';

@Injectable({
  providedIn: 'root'
})
export class Form8Service {

 url: APIform8Connection = new APIform8Connection();

  constructor(private router: Router, private http: HttpClient) { }


  callOnShareData(data: IIncorporationData): Observable<IIncorporationData> {
    return this.http.post<IIncorporationData>(this.url.getCallOnSharesData(), data);
  }

  shareCallSubmit(data: ISubmitCapital): Observable<ISubmitCapital> {
    return this.http.post<ISubmitCapital>(this.url.submitCallOnSharesURL(), data);
  }

  removeDoc(data: IremoveCallShareDoc): Observable<IremoveCallShareDoc> {
    return this.http.post<IremoveCallShareDoc>(this.url.removeDocsURL(), data);
  }

  removeOtherDoc(data: IRemoveOtherDoc): Observable<IRemoveOtherDoc> {
    return this.http.post<IRemoveOtherDoc>(this.url.removeOtherDocsURL(), data);
  }

  resubmit(data: Iresubmit): Observable<Iresubmit> {
    return this.http.post<Iresubmit>(this.url.resubmitURL(), data);
  }

  updateCourtDetails(data: IupdateCourtDetails): Observable<IupdateCourtDetails> {
    return this.http.post<IupdateCourtDetails>(this.url.updateCourtURL(), data);
  }
}
