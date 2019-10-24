import { DomSanitizer } from '@angular/platform-browser';
import { Injectable } from '@angular/core';
import { FormGroup, AbstractControl } from '@angular/forms';
import { GeneralService } from '../services/general.service';

@Injectable({
  providedIn: 'root'
})
export class HelperService {
  fileName: string = 'general.pdf';
  blob: any;
  wordFileName: string = 'Form-13.doc';
  constructor(
    private sanitizer: DomSanitizer,
    private general: GeneralService
  ) {}

  download(
    response: ArrayBuffer,
    token: string = null,
    name: string = null
  ): void {
    if (token !== null) {
      if (name === null) {
        this.general.getDocName(token).subscribe(req => {
          this.fileName = req + '';
        });
      } else {
        this.fileName = name;
      }
      var a = document.createElement('a');
      this.blob = new Blob([new Uint8Array(response)], {
        type: 'application/pdf'
      });
      var url = window.URL.createObjectURL(this.blob);
      a.href = url;
      a.download = this.fileName;
      document.body.appendChild(a);
      a.click();
      setTimeout(function() {
        document.body.removeChild(a);
        window.URL.revokeObjectURL(url);
      }, 100);
    } else {
      this.blob = new Blob([new Uint8Array(response)], {
        type: 'application/pdf'
      });
      const objectUrl = URL.createObjectURL(this.blob);
      window.open(objectUrl, '_blank');
    }
  }

  view(response: ArrayBuffer) {
    this.blob = new Blob([new Uint8Array(response)], { type: 'image/jpeg' });
    const objectUrl = window.URL.createObjectURL(this.blob);
    return objectUrl;
  }

  exportDOC(
    response: string,
    token: string = null,
    name: string = null
  ): void {
    // var header = '<html xmlns:o='urn:schemas-microsoft-com:office:office' '+
    //      'xmlns:w='urn:schemas-microsoft-com:office:word' '+
    //      'xmlns='http://www.w3.org/TR/REC-html40'>'+
    //      '<head><meta charset='utf-8'><title>Export HTML to Word Document with JavaScript</title></head><body>';
    // var footer = '</body></html>';
    var sourceHTML = response;

    var source =
      'data:application/vnd.ms-word;charset=utf-8,' +
      encodeURIComponent(sourceHTML);
    var fileDownload = document.createElement('a');
    document.body.appendChild(fileDownload);
    fileDownload.href = source;
    fileDownload.download = this.wordFileName;
    fileDownload.click();
    document.body.removeChild(fileDownload);
  }
}
