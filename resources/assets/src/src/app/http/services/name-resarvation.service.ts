import { ISearch } from '../models/search.model';
import { Injectable } from '@angular/core';
import { INamereceive } from '../models/search.model';
import { Observable } from 'rxjs';
import { APIConnection } from './connections/APIConnection';
import { HttpClient } from '@angular/common/http';
import { INames, IReSubmit, INameChange } from '../models/recervationdata.model';
import { AuthService } from '../shared/auth.service';

@Injectable({
  providedIn: 'root'
})
export class NameResarvationService {

  url: APIConnection = new APIConnection();

  constructor(private http: HttpClient, private Auth: AuthService) { }

  getSearchResult(data: ISearch, i: number): Observable<ISearch> {
    return this.http.post<ISearch>(this.url.getResultAPI() + (i !== 0 ? '?page=' + i : ''), data);
  }

  getNameReceive(data: INamereceive): Observable<INamereceive> {
    return this.http.post<INamereceive>(this.url.getNameReceiveAPI(), data);
  }

  getReceivedData(i: number, key: string = null): Observable<any> {
    return this.http.post<any>((this.url.getNameReceived() + i), { email: this.Auth.getEmail(), key: key });
  }

  setNameReceive(data: INamereceive): Observable<INamereceive> {
    return this.http.post<INamereceive>(this.url.getNameReceiveAPI(), data);
  }

  searchData(key: string): Observable<INames> {
    return this.http.get<INames>(this.url.getResultAPI() + '?key=' + key + '&email=' + this.Auth.getEmail());
  }

  getNameReservationData(id: number): Observable<INames> {
    return this.http.get<INames>(this.url.getNameReservationDataAPI() + id);
  }

  getFileResubmitData(id: number): Observable<INames> {
    return this.http.post<INames>(this.url.getfileResubmitDataAPI(), { id: id});
  }

  setReSubmitedData(data: IReSubmit): Observable<IReSubmit> {
    return this.http.put<IReSubmit>(this.url.getReSubmitDataAPI(), { data });
  }

  isCheckPostfix(data: string): Observable<any> {
    return this.http.get<any>(this.url.getCheckFixDataAPI() + '?hasfix=' + data);
  }

  nameReSubmitParamiterValidate(companyId: number): Observable<any> {
    return this.http.post<any>(this.url.getCheckNameReSubmitAPI(), { Id: companyId, email: this.Auth.getEmail() });
  }

  nameCancel(id: string, text: string): Observable<any> {
    return this.http.post(this.url.getNameCancelAPI(), { id: id, text: text });
  }

  getNameChangeData(id: string): Observable<INameChange> {
    return this.http.post<INameChange>(this.url.getChangeNameDataAPI(), { id: id });
  }
  /*----------------sahani--------------*/
  getSearchResultSociety(data: ISearch, i: number): Observable<ISearch> {
    return this.http.post<ISearch>(this.url.getResultSocietyAPI() + (++i), data);
  }

}
