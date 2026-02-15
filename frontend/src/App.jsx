import { useEffect, useRef, useState } from 'react'

const KUROMOJI_SCRIPT_SRC = 'https://cdn.jsdelivr.net/npm/kuromoji@0.1.2/build/kuromoji.js'

function App() {
  const [language, setLanguage] = useState('ja')
  const [panel, setPanel] = useState('signin')
  const [isFlipping, setIsFlipping] = useState(false)
  const [signupStep, setSignupStep] = useState(0)
  const [completedUntil, setCompletedUntil] = useState(-1)
  const [signupEmail, setSignupEmail] = useState('')
  const [signupEmailError, setSignupEmailError] = useState('')
  const [signupPassword, setSignupPassword] = useState('')
  const [signupPasswordError, setSignupPasswordError] = useState('')
  const [signupRePassword, setSignupRePassword] = useState('')
  const [signupName, setSignupName] = useState('')
  const [signupNameKana, setSignupNameKana] = useState('')
  const [isNameKanaManuallyEdited, setIsNameKanaManuallyEdited] = useState(false)
  const [signupGender, setSignupGender] = useState('male')
  const [birthYear, setBirthYear] = useState('')
  const [birthMonth, setBirthMonth] = useState('')
  const [birthDay, setBirthDay] = useState('')
  const [postCodeFirst, setPostCodeFirst] = useState('')
  const [postCodeLast, setPostCodeLast] = useState('')
  const [signupPrefecture, setSignupPrefecture] = useState('')
  const [signupCity, setSignupCity] = useState('')
  const [signupTown, setSignupTown] = useState('')
  const [postalLookupError, setPostalLookupError] = useState('')
  const [isPostalLookupLoading, setIsPostalLookupLoading] = useState(false)
  const [viewportSize, setViewportSize] = useState({
    width: typeof window === 'undefined' ? 0 : window.innerWidth,
    height: typeof window === 'undefined' ? 0 : window.innerHeight,
  })

  const midFlipTimerRef = useRef(null)
  const endFlipTimerRef = useRef(null)
  const tokenizerRef = useRef(null)
  const postalLookupRequestIdRef = useRef(0)

  const flipDuration = 560

  const labels = {
    ja: {
      languageLink: 'English Page',
      signIn: {
        title: 'サインイン',
        subtitle: 'アカウントにログイン',
        email: 'メールアドレス',
        password: 'パスワード',
        signIn: 'サインイン',
        signUp: 'サインアップ',
        forgot: 'パスワード忘れた',
      },
      signUp: {
        title: 'サインアップ',
        subtitle: 'ステップ入力',
        steps: [
          'ログイン情報',
          '氏名／住所',
          '雇用情報',
          '資格情報',
          '同意書',
          '雇用条件通知書',
          'NDA',
          '確認画面',
        ],
        next: '次へ',
        back: '戻る',
        generate: '自動生成',
        backToSignIn: 'サインインへ戻る',
        ok: 'OK',
        cancel: 'キャンセル',
        loginInfo: {
          email: 'メールアドレス',
          password: 'パスワード',
          rePassword: 'パスワード（再入力）',
        },
        profileAddress: {
          name: '氏名',
          nameKana: 'ふりがな',
          sex: '性別',
          male: '男性',
          female: '女性',
          none: '未指定',
          birthday: '生年月日',
          year: 'yyyy',
          month: 'MM',
          day: 'dd',
          postCode: '郵便番号',
          postFront: 'xxx',
          postBack: 'yyyy',
          prefecture: '都道府県',
          city: '市区町村',
          town: '町域',
          loading: '住所を取得中...',
          notFound: '該当する郵便番号の住所が見つかりません。',
          apiError: '住所の取得に失敗しました。',
        },
        employment: {
          nationality: '国籍',
          employType: '雇用形態',
          company: '所属会社',
          etcCompany: '所属会社（その他）',
        },
        cert: {
          file: '資格証',
        },
        agreement: {
          text: '同意書（複数行テキスト）',
          agree: '同意する',
        },
        notice: {
          text: '内容（複数行テキスト）',
          agree: '同意する',
        },
        nda: {
          text: '内容（複数行テキスト）',
          agree: '同意する',
        },
        confirm: {
          summary: '入力内容を確認してください。',
        },
        emailErrors: {
          fullWidth: 'メールアドレスに全角文字は使用できません。',
          noAt: 'メールアドレスに「@」を含めてください。',
          noDotInDomain: '「@」以降は「.」を含むドメイン形式で入力してください。',
        },
        passwordErrors: {
          minLength: 'パスワードは8文字以上で入力してください。',
          alphaNumericMix: 'パスワードは英字と数字の組み合わせで入力してください。',
        },
      },
    },
    en: {
      languageLink: '日本語ページ',
      signIn: {
        title: 'Sign In',
        subtitle: 'Access your account',
        email: 'Email Address',
        password: 'Password',
        signIn: 'Sign In',
        signUp: 'Sign Up',
        forgot: 'Forgot Password?',
      },
      signUp: {
        title: 'Sign Up',
        subtitle: 'Step Form',
        steps: [
          'Login Info',
          'Name / Address',
          'Employment',
          'Qualification',
          'Consent Form',
          'Employment Notice',
          'NDA',
          'Confirmation',
        ],
        next: 'Next',
        back: 'Back',
        generate: 'Generate',
        backToSignIn: 'Back to Sign In',
        ok: 'OK',
        cancel: 'Cancel',
        loginInfo: {
          email: 'Email Address',
          password: 'Password',
          rePassword: 'Re-enter Password',
        },
        profileAddress: {
          name: 'Name',
          nameKana: 'Furigana',
          sex: 'Sex',
          male: 'Male',
          female: 'Female',
          none: 'None',
          birthday: 'Birthday',
          year: 'yyyy',
          month: 'MM',
          day: 'dd',
          postCode: 'Post code',
          postFront: 'xxx',
          postBack: 'yyyy',
          prefecture: 'Prefecture',
          city: 'City / Ward',
          town: 'Town',
          loading: 'Loading address...',
          notFound: 'No address found for this postal code.',
          apiError: 'Failed to fetch address.',
        },
        employment: {
          nationality: 'Nationality',
          employType: 'Employment',
          company: 'Affiliated company',
          etcCompany: 'Affiliated company (Other)',
        },
        cert: {
          file: 'Qualification file',
        },
        agreement: {
          text: 'Consent form (multiline text)',
          agree: 'Agree',
        },
        notice: {
          text: 'Content (multiline text)',
          agree: 'Agree',
        },
        nda: {
          text: 'Content (multiline text)',
          agree: 'Agree',
        },
        confirm: {
          summary: 'Please confirm your input.',
        },
        emailErrors: {
          fullWidth: 'Email must not contain full-width characters.',
          noAt: 'Email must include @.',
          noDotInDomain: 'Domain part after @ must include a dot.',
        },
        passwordErrors: {
          minLength: 'Password must be at least 8 characters.',
          alphaNumericMix: 'Password must include both letters and numbers.',
        },
      },
    },
  }

  const t = labels[language]
  const totalSteps = t.signUp.steps.length
  const isLastStep = signupStep === totalSteps - 1

  useEffect(() => {
    return () => {
      if (midFlipTimerRef.current) {
        window.clearTimeout(midFlipTimerRef.current)
      }
      if (endFlipTimerRef.current) {
        window.clearTimeout(endFlipTimerRef.current)
      }
    }
  }, [])

  useEffect(() => {
    const handleResize = () => {
      setViewportSize({
        width: window.innerWidth,
        height: window.innerHeight,
      })
    }

    window.addEventListener('resize', handleResize)
    return () => {
      window.removeEventListener('resize', handleResize)
    }
  }, [])

  useEffect(() => {
    let disposed = false

    const ensureKuromojiGlobal = () => {
      if (globalThis.kuromoji) {
        return Promise.resolve(globalThis.kuromoji)
      }

      return new Promise((resolve, reject) => {
        const existingScript = document.querySelector('script[data-kuromoji="true"]')
        if (existingScript) {
          existingScript.addEventListener('load', () => resolve(globalThis.kuromoji), { once: true })
          existingScript.addEventListener('error', () => reject(new Error('Failed to load kuromoji script')), { once: true })
          return
        }

        const script = document.createElement('script')
        script.src = KUROMOJI_SCRIPT_SRC
        script.async = true
        script.dataset.kuromoji = 'true'
        script.onload = () => resolve(globalThis.kuromoji)
        script.onerror = () => reject(new Error('Failed to load kuromoji script'))
        document.head.appendChild(script)
      })
    }

    ensureKuromojiGlobal()
      .then((kuromojiGlobal) => {
        if (disposed || !kuromojiGlobal?.builder) {
          return
        }

        kuromojiGlobal
          .builder({
            dicPath: 'https://cdn.jsdelivr.net/npm/kuromoji@0.1.2/dict/',
          })
          .build((error, tokenizer) => {
            if (disposed || error) {
              return
            }
            tokenizerRef.current = tokenizer
          })
      })
      .catch(() => {
        tokenizerRef.current = null
      })

    return () => {
      disposed = true
    }
  }, [])

  const flipCard = (nextState) => {
    if (isFlipping) {
      return
    }

    setIsFlipping(true)
    midFlipTimerRef.current = window.setTimeout(() => {
      nextState()
    }, flipDuration / 2)

    endFlipTimerRef.current = window.setTimeout(() => {
      setIsFlipping(false)
    }, flipDuration)
  }

  const toggleLanguage = () => {
    const nextLanguage = language === 'ja' ? 'en' : 'ja'
    flipCard(() => {
      setLanguage(nextLanguage)
    })
  }

  const openSignup = () => {
    flipCard(() => {
      setPanel('signup')
      setSignupStep(0)
      setCompletedUntil(-1)
      setSignupGender('male')
    })
  }

  const backToSignin = () => {
    flipCard(() => {
      setPanel('signin')
      setSignupStep(0)
      setCompletedUntil(-1)
    })
  }

  const goNextStep = () => {
    if (signupStep === 0) {
      const emailError = validateSignupEmail(signupEmail)
      if (emailError) {
        setSignupEmailError(emailError)
        return
      }

      const passwordError = validateSignupPassword(signupPassword)
      if (passwordError) {
        setSignupPasswordError(passwordError)
        return
      }

      setSignupEmailError('')
      setSignupPasswordError('')
    }

    if (isLastStep) {
      return
    }

    setCompletedUntil((prev) => Math.max(prev, signupStep))
    setSignupStep((prev) => prev + 1)
  }

  const goBackStep = () => {
    if (signupStep === 0) {
      backToSignin()
      return
    }

    setSignupStep((prev) => prev - 1)
  }

  const generatePassword = () => {
    const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789!@#$%'
    const randomValues = new Uint32Array(12)
    globalThis.crypto.getRandomValues(randomValues)
    const nextPassword = Array.from(randomValues, (value) => chars.charAt(value % chars.length)).join('')
    setSignupPassword(nextPassword)
    setSignupRePassword(nextPassword)
    setSignupPasswordError(validateSignupPassword(nextPassword))
  }

  const renderInputField = (label, required = true, node = null) => (
    <label className="block">
      <span className="mb-1 block">
        {label}
        {required ? <span className="ml-1 text-[#ffa12f]">*</span> : null}
      </span>
      {node || <input type="text" className="h-10 w-full rounded-lg border border-black/20 px-3 outline-none ring-[#ffa12f] focus:ring-2" />}
    </label>
  )

  const validateSignupEmail = (value) => {
    if (!value) {
      return ''
    }

    const hasFullWidth = /[^\u0020-\u007E]/.test(value)
    if (hasFullWidth) {
      return t.signUp.emailErrors.fullWidth
    }

    if (!value.includes('@')) {
      return t.signUp.emailErrors.noAt
    }

    const domain = value.split('@')[1] || ''
    if (!domain.includes('.')) {
      return t.signUp.emailErrors.noDotInDomain
    }

    return ''
  }

  const validateSignupPassword = (value) => {
    if (!value) {
      return ''
    }

    if (value.length < 8) {
      return t.signUp.passwordErrors.minLength
    }

    const hasAlphabet = /[A-Za-z]/.test(value)
    const hasNumber = /\d/.test(value)
    if (!hasAlphabet || !hasNumber) {
      return t.signUp.passwordErrors.alphaNumericMix
    }

    return ''
  }

  const toHalfWidthAlphaNum = (value) =>
    value.replace(/[Ａ-Ｚａ-ｚ０-９]/g, (char) => String.fromCharCode(char.charCodeAt(0) - 65248))

  const toHiragana = (value) =>
    value.replace(/[ァ-ヶ]/g, (char) => String.fromCharCode(char.charCodeAt(0) - 0x60))

  const generateFuriganaFromName = (value) => {
    if (!value) {
      return ''
    }

    const tokenizer = tokenizerRef.current
    if (tokenizer) {
      const tokens = tokenizer.tokenize(value)
      const readingText = tokens
        .map((token) => (token.reading && token.reading !== '*' ? token.reading : token.surface_form))
        .join('')
      const normalizedReading = toHiragana(toHalfWidthAlphaNum(readingText))
      return normalizedReading.replace(/\s+/g, ' ').trim()
    }

    const normalized = toHiragana(toHalfWidthAlphaNum(value))
    return normalized.replace(/\s+/g, ' ').trim()
  }

  const handleNameChange = (value) => {
    setSignupName(value)
    if (!isNameKanaManuallyEdited) {
      setSignupNameKana(generateFuriganaFromName(value))
    }
  }

  const handleNameKanaChange = (value) => {
    setSignupNameKana(value)
    setIsNameKanaManuallyEdited(value.length > 0)
  }

  const digitsOnly = (value, maxLength) => value.replace(/[^0-9]/g, '').slice(0, maxLength)

  const lookupAddressByZipCode = async (zipCode) => {
    const requestId = postalLookupRequestIdRef.current + 1
    postalLookupRequestIdRef.current = requestId

    const configuredEndpointRaw = (import.meta.env.VITE_ADDRESS_API_PATH || '').trim()
    const configuredEndpoint = configuredEndpointRaw
      ? (/^https?:\/\//.test(configuredEndpointRaw)
          ? configuredEndpointRaw
          : `/${configuredEndpointRaw.replace(/^\/+/, '')}`)
      : ''

    const endpointCandidates = configuredEndpoint
      ? [configuredEndpoint]
      : ['/api/searchAddress/', '/api/searchAddress']

    const uniqEndpointCandidates = Array.from(new Set(endpointCandidates))

    setIsPostalLookupLoading(true)
    setPostalLookupError('')

    let lookupErrorMessage = t.signUp.profileAddress.apiError

    try {
      for (const endpoint of uniqEndpointCandidates) {
        let response

        try {
          response = await fetch(`${endpoint}?zipcode=${encodeURIComponent(zipCode)}`, {
            headers: {
              Accept: 'application/json',
            },
          })
        } catch {
          continue
        }

        if (postalLookupRequestIdRef.current !== requestId) {
          return
        }

        let body = null
        const contentType = response.headers.get('content-type') || ''
        if (contentType.includes('application/json')) {
          try {
            body = await response.json()
          } catch {
            body = null
          }
        }

        if (response.ok && body?.success && body?.address) {
          setSignupPrefecture(body.address.prefecture || '')
          setSignupCity(body.address.city || '')
          setSignupTown(body.address.town || '')
          setPostalLookupError('')
          return
        }

        if (response.status === 404) {
          lookupErrorMessage = body?.message || t.signUp.profileAddress.notFound
          continue
        }

        lookupErrorMessage = body?.message || t.signUp.profileAddress.apiError
      }

      setSignupPrefecture('')
      setSignupCity('')
      setSignupTown('')
      setPostalLookupError(lookupErrorMessage)
    } catch {
      if (postalLookupRequestIdRef.current !== requestId) {
        return
      }

      setSignupPrefecture('')
      setSignupCity('')
      setSignupTown('')
      setPostalLookupError(lookupErrorMessage)
    } finally {
      if (postalLookupRequestIdRef.current === requestId) {
        setIsPostalLookupLoading(false)
      }
    }
  }

  const updatePostalCodeAndLookup = (nextFirst, nextLast) => {
    const zipCode = `${nextFirst}${nextLast}`

    setPostCodeFirst(nextFirst)
    setPostCodeLast(nextLast)

    if (zipCode.length !== 7) {
      setIsPostalLookupLoading(false)
      setPostalLookupError('')
      return
    }

    lookupAddressByZipCode(zipCode)
  }

  const renderStepBody = () => {
    switch (signupStep) {
      case 0:
        return (
          <div className="grid grid-cols-1 gap-3 lg:grid-cols-2">
            {renderInputField(
              t.signUp.loginInfo.email,
              true,
              <input
                type="email"
                inputMode="email"
                autoComplete="email"
                autoCapitalize="none"
                spellCheck={false}
                lang="en"
                value={signupEmail}
                onChange={(event) => {
                  const nextValue = event.target.value
                  setSignupEmail(nextValue)
                  setSignupEmailError(validateSignupEmail(nextValue))
                }}
                className="h-10 w-full rounded-lg border border-black/20 px-3 outline-none ring-[#ffa12f] focus:ring-2"
              />,
            )}
            {signupEmailError ? (
              <p className="-mt-2 text-[11px] font-bold text-red-600 lg:col-span-2">{signupEmailError}</p>
            ) : null}
            {renderInputField(
              t.signUp.loginInfo.password,
              true,
              <div className="relative">
                <input
                  type="password"
                  inputMode="text"
                  autoComplete="new-password"
                  autoCapitalize="none"
                  spellCheck={false}
                  lang="en"
                  value={signupPassword}
                  onChange={(event) => {
                    const nextValue = event.target.value
                    setSignupPassword(nextValue)
                    setSignupPasswordError(validateSignupPassword(nextValue))
                  }}
                  className="h-10 w-full rounded-lg border border-black/20 pl-3 pr-24 outline-none ring-[#ffa12f] focus:ring-2"
                />
                <button
                  type="button"
                  className="absolute top-1/2 right-2 h-7 -translate-y-1/2 rounded-md bg-[#c2c2c2] px-2 text-[11px] font-bold text-black"
                  onClick={() => generatePassword()}
                >
                  {t.signUp.generate}
                </button>
              </div>,
            )}
            {signupPasswordError ? (
              <p className="-mt-2 text-[11px] font-bold text-red-600 lg:col-span-2">{signupPasswordError}</p>
            ) : null}
            <div className="lg:col-span-2">
              {renderInputField(
                t.signUp.loginInfo.rePassword,
                true,
                <input
                  type="password"
                  inputMode="text"
                  autoComplete="new-password"
                  autoCapitalize="none"
                  spellCheck={false}
                  lang="en"
                  value={signupRePassword}
                  onChange={(event) => setSignupRePassword(event.target.value)}
                  className="h-10 w-full rounded-lg border border-black/20 px-3 outline-none ring-[#ffa12f] focus:ring-2"
                />,
              )}
            </div>
          </div>
        )
      case 1:
        return (
          <div className="grid grid-cols-1 gap-3 lg:grid-cols-2" key="profile-address-step">
            {renderInputField(
              t.signUp.profileAddress.name,
              true,
              <input
                type="text"
                value={signupName}
                onChange={(event) => handleNameChange(event.target.value)}
                className="h-10 w-full rounded-lg border border-black/20 px-3 outline-none ring-[#ffa12f] focus:ring-2"
              />,
            )}
            {renderInputField(
              t.signUp.profileAddress.nameKana,
              false,
              <input
                type="text"
                value={signupNameKana}
                onChange={(event) => handleNameKanaChange(event.target.value)}
                className="h-10 w-full rounded-lg border border-black/20 px-3 outline-none ring-[#ffa12f] focus:ring-2"
              />,
            )}
            {renderInputField(
              t.signUp.profileAddress.sex,
              true,
              <select
                value={signupGender}
                onChange={(event) => setSignupGender(event.target.value)}
                className="h-10 w-full rounded-lg border border-black/20 px-3 outline-none ring-[#ffa12f] focus:ring-2"
              >
                <option value="">Select</option>
                <option value="male">{t.signUp.profileAddress.male}</option>
                <option value="female">{t.signUp.profileAddress.female}</option>
                <option value="none">{t.signUp.profileAddress.none}</option>
              </select>,
            )}
            {renderInputField(
              t.signUp.profileAddress.birthday,
              true,
              <div className="flex items-center gap-2">
                <input
                  type="text"
                  inputMode="numeric"
                  pattern="[0-9]*"
                  placeholder={t.signUp.profileAddress.year}
                  value={birthYear}
                  onChange={(event) => setBirthYear(digitsOnly(event.target.value, 4))}
                  className="h-10 w-full rounded-lg border border-black/20 px-3 outline-none ring-[#ffa12f] focus:ring-2"
                />
                <input
                  type="text"
                  inputMode="numeric"
                  pattern="[0-9]*"
                  placeholder={t.signUp.profileAddress.month}
                  value={birthMonth}
                  onChange={(event) => setBirthMonth(digitsOnly(event.target.value, 2))}
                  className="h-10 w-full rounded-lg border border-black/20 px-3 outline-none ring-[#ffa12f] focus:ring-2"
                />
                <input
                  type="text"
                  inputMode="numeric"
                  pattern="[0-9]*"
                  placeholder={t.signUp.profileAddress.day}
                  value={birthDay}
                  onChange={(event) => setBirthDay(digitsOnly(event.target.value, 2))}
                  className="h-10 w-full rounded-lg border border-black/20 px-3 outline-none ring-[#ffa12f] focus:ring-2"
                />
              </div>,
            )}
            {renderInputField(
              t.signUp.profileAddress.postCode,
              true,
              <div className="flex items-center gap-2">
                <input
                  type="text"
                  inputMode="numeric"
                  pattern="[0-9]*"
                  placeholder={t.signUp.profileAddress.postFront}
                  value={postCodeFirst}
                  onChange={(event) => {
                    const nextFirst = digitsOnly(event.target.value, 3)
                    updatePostalCodeAndLookup(nextFirst, postCodeLast)
                  }}
                  className="h-10 w-full rounded-lg border border-black/20 px-3 outline-none ring-[#ffa12f] focus:ring-2"
                />
                <span className="font-bold">-</span>
                <input
                  type="text"
                  inputMode="numeric"
                  pattern="[0-9]*"
                  placeholder={t.signUp.profileAddress.postBack}
                  value={postCodeLast}
                  onChange={(event) => {
                    const nextLast = digitsOnly(event.target.value, 4)
                    updatePostalCodeAndLookup(postCodeFirst, nextLast)
                  }}
                  className="h-10 w-full rounded-lg border border-black/20 px-3 outline-none ring-[#ffa12f] focus:ring-2"
                />
              </div>,
            )}
            {isPostalLookupLoading ? (
              <p className="-mt-2 text-[11px] font-bold text-black/70 lg:col-span-2">{t.signUp.profileAddress.loading}</p>
            ) : null}
            {postalLookupError ? (
              <p className="-mt-2 text-[11px] font-bold text-red-600 lg:col-span-2">{postalLookupError}</p>
            ) : null}
            <div className="lg:col-span-2">
              {renderInputField(
                t.signUp.profileAddress.prefecture,
                true,
                <input
                  type="text"
                  value={signupPrefecture}
                  onChange={(event) => setSignupPrefecture(event.target.value)}
                  className="h-10 w-full rounded-lg border border-black/20 px-3 outline-none ring-[#ffa12f] focus:ring-2"
                />,
              )}
            </div>
            {renderInputField(
              t.signUp.profileAddress.city,
              true,
              <input
                type="text"
                value={signupCity}
                onChange={(event) => setSignupCity(event.target.value)}
                className="h-10 w-full rounded-lg border border-black/20 px-3 outline-none ring-[#ffa12f] focus:ring-2"
              />,
            )}
            {renderInputField(
              t.signUp.profileAddress.town,
              true,
              <input
                type="text"
                value={signupTown}
                onChange={(event) => setSignupTown(event.target.value)}
                className="h-10 w-full rounded-lg border border-black/20 px-3 outline-none ring-[#ffa12f] focus:ring-2"
              />,
            )}
          </div>
        )
      case 2:
        return (
          <div className="grid grid-cols-1 gap-3 lg:grid-cols-2">
            {renderInputField(t.signUp.employment.nationality)}
            {renderInputField(
              t.signUp.employment.employType,
              true,
              <select className="h-10 w-full rounded-lg border border-black/20 px-3 outline-none ring-[#ffa12f] focus:ring-2">
                <option>Part-time</option>
                <option>Dispatch</option>
                <option>Sole-proprietor</option>
              </select>,
            )}
            {renderInputField(t.signUp.employment.company)}
            {renderInputField(t.signUp.employment.etcCompany, false)}
          </div>
        )
      case 3:
        return (
          <div className="grid grid-cols-1 gap-3">
            {renderInputField(
              t.signUp.cert.file,
              true,
              <input
                type="file"
                accept="image/*"
                className="h-10 w-full rounded-lg border border-black/20 px-3 pt-2 outline-none ring-[#ffa12f] file:mr-2 file:rounded-md file:border-0 file:bg-[#c2c2c2] file:px-2 file:py-1 file:text-[11px] file:font-bold file:text-black focus:ring-2"
              />,
            )}
          </div>
        )
      case 4:
        return (
          <div className="grid grid-cols-1 gap-3">
            <label className="block">
              <span className="mb-1 block">{t.signUp.agreement.text}<span className="ml-1 text-[#ffa12f]">*</span></span>
              <textarea rows={6} className="w-full rounded-lg border border-black/20 px-3 py-2 outline-none ring-[#ffa12f] focus:ring-2" defaultValue="" />
            </label>
            <label className="flex h-10 items-center gap-2 rounded-lg border border-black/20 px-3">
              <input type="checkbox" />
              <span>{t.signUp.agreement.agree}</span>
            </label>
          </div>
        )
      case 5:
        return (
          <div className="grid grid-cols-1 gap-3">
            <label className="block">
              <span className="mb-1 block">{t.signUp.notice.text}<span className="ml-1 text-[#ffa12f]">*</span></span>
              <textarea rows={6} className="w-full rounded-lg border border-black/20 px-3 py-2 outline-none ring-[#ffa12f] focus:ring-2" defaultValue="" />
            </label>
            <label className="flex h-10 items-center gap-2 rounded-lg border border-black/20 px-3">
              <input type="checkbox" />
              <span>{t.signUp.notice.agree}</span>
            </label>
          </div>
        )
      case 6:
        return (
          <div className="grid grid-cols-1 gap-3">
            <label className="block">
              <span className="mb-1 block">{t.signUp.nda.text}<span className="ml-1 text-[#ffa12f]">*</span></span>
              <textarea rows={6} className="w-full rounded-lg border border-black/20 px-3 py-2 outline-none ring-[#ffa12f] focus:ring-2" defaultValue="" />
            </label>
            <label className="flex h-10 items-center gap-2 rounded-lg border border-black/20 px-3">
              <input type="checkbox" />
              <span>{t.signUp.nda.agree}</span>
            </label>
          </div>
        )
      case 7:
      default:
        return (
          <div className="rounded-lg border border-black/20 p-4 text-[12px]">
            {t.signUp.confirm.summary}
          </div>
        )
    }
  }

  const renderStepControls = () => {
    if (isLastStep) {
      return (
        <div className="mt-4 grid grid-cols-2 gap-3">
          <button type="button" className="h-11 rounded-lg bg-[#009e00] text-[12px] font-bold text-white">
            {t.signUp.ok}
          </button>
          <button
            type="button"
            className="h-11 rounded-lg bg-[#c2c2c2] text-[12px] font-bold text-black"
            onClick={() => backToSignin()}
          >
            {t.signUp.cancel}
          </button>
        </div>
      )
    }

    return (
      <div className="mt-4 grid grid-cols-2 gap-3">
        <button
          type="button"
          className="h-11 rounded-lg bg-[#c2c2c2] text-[12px] font-bold text-black"
          onClick={() => goBackStep()}
        >
          {signupStep === 0 ? t.signUp.backToSignIn : t.signUp.back}
        </button>
        <button
          type="button"
          className="h-11 rounded-lg bg-[#009e00] text-[12px] font-bold text-white"
          onClick={() => goNextStep()}
        >
          {t.signUp.next}
        </button>
      </div>
    )
  }

  const renderSignupWizard = () => (
    <div>
      <div className="top-accent-line mb-5" aria-hidden="true" />
      <div className="flex items-start justify-between gap-4">
        <div>
          <h1 className="heading-primary">{t.signUp.title}</h1>
          <p className="mt-1 text-[12px] text-black/70">{t.signUp.subtitle}</p>
          <p className="mt-1 text-[12px] font-bold text-black">{t.signUp.steps[signupStep]}</p>
        </div>
        <a
          href="#"
          className="pt-1 text-[12px] font-bold text-[#2b61dd] underline decoration-1 underline-offset-2 hover:decoration-2"
          onClick={(event) => {
            event.preventDefault()
            toggleLanguage()
          }}
        >
          {t.languageLink}
        </a>
      </div>

      <div className="mt-5">
        <form onSubmit={(event) => event.preventDefault()}>{renderStepBody()}</form>
      </div>

      <div className="mt-5 border-t border-black/10 pt-4">
        <div className="flex items-center justify-center gap-3">
          {t.signUp.steps.map((_, index) => {
            const done = index <= completedUntil
            return (
              <span
                key={`dot-${index}`}
                className={`inline-block h-2 w-2 rounded-full border border-[#ffa12f] ${done ? 'bg-[#ffa12f]' : 'bg-white'}`}
              />
            )
          })}
        </div>
        {renderStepControls()}
      </div>
    </div>
  )

  const renderSignIn = () => (
    <>
      <div className="top-accent-line mb-5" aria-hidden="true" />
      <div className="flex items-start justify-between gap-4">
        <div>
          <h1 className="heading-primary">{t.signIn.title}</h1>
          <p className="mt-1 text-[12px] text-black/70">{t.signIn.subtitle}</p>
        </div>
        <a
          href="#"
          className="pt-1 text-[12px] font-bold text-[#2b61dd] underline decoration-1 underline-offset-2 hover:decoration-2"
          onClick={(event) => {
            event.preventDefault()
            toggleLanguage()
          }}
        >
          {t.languageLink}
        </a>
      </div>

      <form className="mt-6 space-y-4" onSubmit={(event) => event.preventDefault()}>
        <label className="block">
          <span className="mb-1 block">{t.signIn.email}</span>
          <input
            type="email"
            name="email"
            inputMode="email"
            autoComplete="email"
            autoCapitalize="none"
            spellCheck={false}
            lang="en"
            className="h-11 w-full rounded-lg border border-black/20 px-3 outline-none ring-[#ffa12f] focus:ring-2"
          />
        </label>

        <label className="block">
          <span className="mb-1 block">{t.signIn.password}</span>
          <input
            type="password"
            name="password"
            inputMode="text"
            autoComplete="current-password"
            autoCapitalize="none"
            spellCheck={false}
            lang="en"
            className="h-11 w-full rounded-lg border border-black/20 px-3 outline-none ring-[#ffa12f] focus:ring-2"
          />
        </label>

        <button
          type="submit"
          className="mt-2 h-11 w-full rounded-lg bg-[#009e00] text-[12px] font-bold text-white"
        >
          {t.signIn.signIn}
        </button>
      </form>

      <div className="mt-5 grid grid-cols-2 gap-3 text-[12px]">
        <a
          href="#"
          className="flex h-10 items-center justify-center rounded-lg bg-[#c2c2c2] font-bold text-black"
          onClick={(event) => {
            event.preventDefault()
            openSignup()
          }}
        >
          {t.signIn.signUp}
        </a>
        <a href="#" className="flex h-10 items-center justify-center rounded-lg bg-[#c2c2c2] font-bold text-black">
          {t.signIn.forgot}
        </a>
      </div>
    </>
  )

  return (
    <main className={`relative flex min-h-screen items-center justify-center overflow-hidden bg-white px-5 md:px-8 text-black ${panel === 'signup' ? 'pt-10 pb-6 md:pt-12 md:pb-8' : 'py-6 md:py-8'}`}>
      <a
        href="#"
        className="absolute top-4 left-5 z-30 text-[12px] font-bold text-[#2b61dd] underline decoration-1 underline-offset-2 hover:decoration-2 md:left-8"
        onClick={(event) => {
          event.preventDefault()
          window.location.reload()
        }}
      >
        Top
      </a>

      <div className="top-geo-grid" aria-hidden="true" />
      <div className="top-geo-dots" aria-hidden="true" />
      <div className="top-accent-orb" aria-hidden="true" />

      <section className={`card-layer-wrap w-full ${panel === 'signup' ? 'max-w-6xl' : 'max-w-md md:max-w-lg'}`}>
        <div className="card-bg-mask" aria-hidden="true" />
        <div className="relative z-20">
          <article className={`card-shell rounded-2xl border border-black/10 bg-white p-6 shadow-sm md:p-8 ${panel === 'signup' ? 'lg:p-10' : ''} ${isFlipping ? 'card-flip-once' : ''}`}>
            {panel === 'signin' ? renderSignIn() : renderSignupWizard()}
          </article>
        </div>
      </section>

      <div className="fixed right-0 bottom-0 left-0 z-40 bg-[dodgerblue] px-3 py-1 text-[12px] font-bold text-white">
        画面サイズ：W{viewportSize.width} H{viewportSize.height}
      </div>
    </main>
  )
}

export default App
