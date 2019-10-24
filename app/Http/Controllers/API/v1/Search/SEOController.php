<?php

namespace App\Http\Controllers\API\v1\Search;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Company;
use App\Http\Resources\SearchCollection as SearchResources;
use App\Http\Controllers\API\v1\Search\Validation\ValidationRules;
use App\Http\Helper\_helper;
use App\Society;

class SEOController extends Controller
{

    use ValidationRules, _helper;

    public function showName(Request $request)
    {
       
        if (env('PUB_URL_TOKEN') == $request->token) {

            $originalWord = strtoupper($request->searchtext);
            
            $originalWord =  trim(preg_replace('/\s+/', ' ', strtoupper($originalWord)));

            $searchWord = $this->seo_friendly_url($originalWord);

            $separetWord = preg_replace('/\s+/', '|',str_replace(array('.', ''),'',strtoupper($searchWord)));
           
            $trimpvnumber =  trim(preg_replace('/\s+/', '', strtoupper($searchWord)));

            $iLikeSearch =  trim(preg_replace('/\s+/', ' ', strtoupper($searchWord)));
     
            $notIn = array(
                $this->settings('COMPANY_NAME_CANCELED', 'key')->id,
                $this->settings('COMPANY_NAME_PROCESSING', 'key')->id,
                $this->settings('COMPANY_NAME_EXPIRED', 'key')->id,
                $this->settings('COMPANY_NAME_REJECTED', 'key')->id,
                $this->settings('COMPANY_NAME_CHANGE_REJECTED', 'key')->id
            );

            $pvdata = Company::leftJoin('company_certificate', 'companies.id', '=', 'company_certificate.company_id')
                    ->select('companies.id','companies.name','companies.postfix','company_certificate.registration_no')
                    ->whereNotIn('companies.status', $notIn)
                    ->Where('company_certificate.registration_no', 'ilike', '%'. $trimpvnumber  .'%')
                    ->first();
                       
            $data= Company::leftJoin('company_certificate', 'companies.id', '=', 'company_certificate.company_id')
                    ->select('companies.id','companies.name','companies.postfix','company_certificate.registration_no')
                    ->whereNotIn('companies.status', $notIn)
                    ->when($request, function ($query) use ($request, $iLikeSearch ,$separetWord, $trimpvnumber) {
                        if($request->criteria == 1 ){
                            $query->whereRaw('to_tsvector(\'english_nostop\',eroc_companies.name ||\' \'|| eroc_companies.postfix) @@ to_tsquery(\'english_nostop\',quote_literal(?))', [$separetWord])
                                            ->orWhere('company_certificate.registration_no', 'ilike', '%'. $trimpvnumber .'%');
                        }else{
                            $query->whereRaw('concat(trim(eroc_companies.name),\' \',trim(eroc_companies.postfix)) ilike (?)', $iLikeSearch. '%')
                            ->orWhere('company_certificate.registration_no', 'ilike',  $trimpvnumber .'%');
                        }
                    })
                    ->when($request, function ($query) use ($request, $separetWord, $pvdata, $iLikeSearch) {
                        if($request->criteria == 1 ){
                            if(is_null($pvdata)){
                                $query->orderByRaw('ts_rank(to_tsvector(\'english_nostop\',eroc_companies.name||\' \'||eroc_companies.postfix), to_tsquery(\'english_nostop\',quote_literal(?))) DESC', [$separetWord]);
                            }else {
                                $query->orderByRaw('ts_rank(to_tsvector(\'english_nostop\',eroc_company_certificate.registration_no), to_tsquery(\'english_nostop\',?)) DESC', [$separetWord]);
                            }
                        }
                    })
                    ->paginate(10);
                     
            $hasData = Company::leftJoin('company_certificate', 'companies.id', '=', 'company_certificate.company_id')
                ->select('companies.id', 'companies.name', 'companies.postfix', 'company_certificate.registration_no')
                ->whereNotIn('companies.status', $notIn)
                ->where(function ($query) use ($originalWord) {
                     $query->whereRaw('concat(trim(eroc_companies.name),\' \',trim(eroc_companies.postfix)) = (?)', $originalWord)
                    ->orWhereRaw('trim(eroc_companies.name) = (?)', $originalWord);
                })
                ->get();
          
            $req = [
                'available' => $hasData->isEmpty() ? true : false,
                'data' => $hasData->isEmpty() ? $this->availableName($request) : array()
            ];

            $merge = [
                'availableData' => new SearchResources($data),
                'notHasData' => $req
            ];

            return $merge;

        } else {
            return response()->json(['error' => 'Error decoding authentication request.']);
        }
    }

    function seo_friendly_url($string){
        $string = str_replace(array('[\', \']'), '', $string);
        $string = preg_replace('/\[.*\]/U', '', $string); 
        $string = preg_replace('/&(amp;)?#?[a-z0-9]+;/i', '', $string);
        $string = preg_replace('/&([a-z])(acute|uml|circ|grave|ring|cedil|slash|tilde|caron|lig|quot|rsquo);/i', '\\1', $string ); 
        return trim($string);
    }

    public function availableName(Request $request)
    {
        $result = $this->checkRules($request->searchtext, $request->companyType);

        return $this->getfailsValidation($result);
    }

    private function getfailsValidation($result)
    {
        if (is_array($result)) {
            foreach ($result as $key => &$arrayElement) {
                if ($arrayElement['status'] != 'fail') {
                    unset($result[$key]);
                }
            }
        }

        return array_values($result);
    }

    private function getClearCombination($result)
    {
        return trim(str_replace($this->getRestrictedWord(),'',$result));
    }


    //----------------sahani--------------
    public function showNameSociety(Request $request)
    {
        if (env('PUB_URL_TOKEN') == $request->token) {

            $originalWord = strtoupper($request->searchtext);

            $searchWord = $this->seo_friendly_url($originalWord);

            $separetWord = preg_replace('/\s+/', ' & ',str_replace(array('.', ''),',',strtoupper($searchWord)));

            $iLikeSearch =  trim(preg_replace('/\s+/', '', strtoupper($searchWord)));

            $data = Society::select('name','status')
                    ->Where('name', 'ilike', '%'. $iLikeSearch  .'%')->orWhere('name', '=', $originalWord)->paginate(10);

            $notIn = array(
                        $this->settings('SOCIETY_CANCELED', 'key')->id,
                        $this->settings('SOCIETY_REJECTED', 'key')->id
                        
                    );        

            $hasData = Society::select('name')
                    ->whereNotIn('societies.status', $notIn)
                    ->when($request, function ($query) use ($request, $originalWord ) {
                        $query->where(\DB::raw("TRIM(eroc_societies.name)"), '=', $originalWord);
                    })->first();

            $req = [
                'available' => is_null($hasData) ? true : false,
                'data' => is_null($hasData) ? $this->availableName($request) : array()
            ];

            $merge = [
                'availableData' => new SearchResources($data),
                'notHasData' => $req
            ];

            return $merge;

        } else {
            return response()->json(['error' => 'Error decoding authentication request.']);
        }
    }

 // --------end ----sahani--------------------

}
