import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { HttpClient } from '@angular/common/http';
import { APIConnection } from './connections/APIConnection';

@Injectable({
  providedIn: 'root'
})
export class ReductionCapitalService {


  url: APIConnection = new APIConnection();

  constructor(private http: HttpClient) { }

  getRecRowData(id: string): Observable<any> {
    return this.http.post<any>(this.url._getReduRowAPI(), { id: id });
  }

  setRecDataRaw(id: string, build_id: string, jobId: string): Observable<any> {
    return this.http.post<any>(this.url._getReduAPI(), { id: id, secId: build_id, jobId : jobId });
  }
  updateReduction(dt: any): Observable<any> {
    return this.http.post<any>(this.url._getUpdateReduAPI(), { data: dt });
  }
  updateAndContinue(id: string): Observable<any> {
    return this.http.post<any>(this.url._getUpdateReduAPI(), { id: id });
  }
  getCapitalFormFill(data: any) {
    return this.http.post(this.url.getCapitalFormAPI(), { data }, { responseType: 'arraybuffer' });
  }
  setResubmit(id: any) {
    return this.http.put(this.url.getCapResubmitAPI(), { id: id });
  }
}
