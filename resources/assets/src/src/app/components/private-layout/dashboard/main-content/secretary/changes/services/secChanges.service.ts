import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Router } from '@angular/router';
import { Observable } from 'rxjs';
import { APIsecChangesConnection } from './connections/secChangesConnection';
import { IgetData, IUpdateAlterType, IUpdateName, IUpdateAddress, IUpdateEmail, IUpdateContact, IRemoveOtherDoc, IUpdateFirmName, IUpdateSecFirmPartner, IRemoveecFirmPartner, ICheckNICPartner, IADDSecFirmPartner, ISubmitRequest, IResubmitRequest } from '../models/secChanges.model';

@Injectable({
  providedIn: 'root'
})
export class SecChangesService {

 url: APIsecChangesConnection = new APIsecChangesConnection();

  constructor(private router: Router, private http: HttpClient) { }

  // secretory individual services
  getSecretaryData(data: IgetData): Observable<IgetData> {
    return this.http.post<IgetData>(this.url.getSecData(), data);
  }
  getSecretaryHeavyData(data: IgetData): Observable<IgetData> {
    return this.http.post<IgetData>(this.url.getSecHeavyData(), data);
  }
  updateSecAlterationType(data: IUpdateAlterType): Observable<IUpdateAlterType> {
    return this.http.post<IUpdateAlterType>(this.url.updateSecAlterTypes(), data);
  }
  updateSecName(data: IUpdateName): Observable<IUpdateName> {
    return this.http.post<IUpdateName>(this.url.updateSecName(), data);
  }
  updateSecAddress(data: IUpdateAddress): Observable<IUpdateAddress> {
    return this.http.post<IUpdateAddress>(this.url.updateSecAddress(), data);
  }
  updateSecEmail(data: IUpdateEmail): Observable<IUpdateEmail> {
    return this.http.post<IUpdateEmail>(this.url.updateSecEmail(), data);
  }
  updateSecContact(data: IUpdateContact): Observable<IUpdateContact> {
    return this.http.post<IUpdateContact>(this.url.updateSecContact(), data);
  }
  removeSecOtherDoc(data: IRemoveOtherDoc): Observable<IRemoveOtherDoc> {
    return this.http.post<IRemoveOtherDoc>(this.url.removeOtherDocURL(), data);
  }

  submitSecRequest(data: ISubmitRequest): Observable<ISubmitRequest> {
    return this.http.post<ISubmitRequest>(this.url.submitSecRequest(), data);
  }
  resubmitSecRequest(data: IResubmitRequest): Observable<IResubmitRequest> {
    return this.http.post<IResubmitRequest>(this.url.resubmitSecRequest(), data);
  }

  // secretory firm services
  getSecretaryFirmData(data: IgetData): Observable<IgetData> {
    return this.http.post<IgetData>(this.url.getSecFirmData(), data);
  }
  getSecretaryFirmHeavyData(data: IgetData): Observable<IgetData> {
    return this.http.post<IgetData>(this.url.getSecFirmHeavyData(), data);
  }
  updateSecFirmAlterationType(data: IUpdateAlterType): Observable<IUpdateAlterType> {
    return this.http.post<IUpdateAlterType>(this.url.updateSecFirmAlterTypes(), data);
  }
  updateSecFirmName(data: IUpdateName): Observable<IUpdateFirmName> {
    return this.http.post<IUpdateFirmName>(this.url.updateSecFirmName(), data);
  }
  updateSecFirmAddress(data: IUpdateAddress): Observable<IUpdateAddress> {
    return this.http.post<IUpdateAddress>(this.url.updateSecFirmAddress(), data);
  }
  updateSecFirmEmail(data: IUpdateEmail): Observable<IUpdateEmail> {
    return this.http.post<IUpdateEmail>(this.url.updateSecFirmEmail(), data);
  }
  updateSecFirmContact(data: IUpdateContact): Observable<IUpdateContact> {
    return this.http.post<IUpdateContact>(this.url.updateSecFirmContact(), data);
  }
  removeSecFirmOtherDoc(data: IRemoveOtherDoc): Observable<IRemoveOtherDoc> {
    return this.http.post<IRemoveOtherDoc>(this.url.removeOtherDocFirmURL(), data);
  }
  addSecFirmPartner(data: IADDSecFirmPartner): Observable<IADDSecFirmPartner> {
    return this.http.post<IADDSecFirmPartner>(this.url.addSecFirmPartner(), data);
  }
  updateSecFirmPartner(data: IUpdateSecFirmPartner): Observable<IUpdateSecFirmPartner> {
    return this.http.post<IUpdateSecFirmPartner>(this.url.updateSecFirmPartner(), data);
  }
  removeSecFirmPartner(data: IRemoveecFirmPartner): Observable<IRemoveecFirmPartner> {
    return this.http.post<IRemoveecFirmPartner>(this.url.removeSecFirmPartner(), data);
  }
  checkNicPartner(data: ICheckNICPartner): Observable<ICheckNICPartner> {
    return this.http.post<ICheckNICPartner>(this.url.checkNicPartner(), data);
  }
  submitSecFirmRequest(data: ISubmitRequest): Observable<ISubmitRequest> {
    return this.http.post<ISubmitRequest>(this.url.submitSecFirmRequest(), data);
  }
  resubmitSecFirmRequest(data: IResubmitRequest): Observable<IResubmitRequest> {
    return this.http.post<IResubmitRequest>(this.url.resubmitSecFirmRequest(), data);
  }

}
